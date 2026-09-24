<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected Supplier $supplier;

    protected Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pharmacist Tester',
            'email' => 'pharmacist@test.com',
            'role' => User::ROLE_PHARMACIST,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $this->category = Category::create([
            'name' => 'Antibiotics',
            'slug' => 'antibiotics',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PharmaDistro Ltd',
            'status' => 'active',
        ]);

        $this->medicine = Medicine::create([
            'category_id' => $this->category->id,
            'name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin',
            'dosage_form' => 'Capsule',
            'strength' => '500mg',
            'unit' => 'Box',
            'barcode' => '111222333',
            'reorder_level' => 15,
            'selling_price' => 50.00,
            'status' => 'active',
        ]);
    }

    public function test_fefo_stock_deduction_prioritizes_earliest_expiry_batch(): void
    {
        // Batch A: Expiring in 2 months, qty = 10
        $batchA = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-A-EARLY',
            'quantity' => 10,
            'purchase_price' => 30.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->addMonths(2)->toDateString(),
            'status' => 'active',
        ]);

        // Batch B: Expiring in 12 months, qty = 20
        $batchB = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-B-LATE',
            'quantity' => 20,
            'purchase_price' => 32.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-001',
            'sale_date' => now(),
            'subtotal' => 750.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => 750.00,
            'amount_paid' => 750.00,
            'change_due' => 0.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sold_by' => $this->user->id,
        ]);

        $inventoryService = app(InventoryService::class);

        // Deduct 15 units (should fully deplete Batch A with 10 units, and take 5 from Batch B)
        $items = $inventoryService->deductStockFEFO($sale, $this->medicine->id, 15, 50.00, 0, $this->user->id);

        $this->assertCount(2, $items);

        $batchA->refresh();
        $batchB->refresh();

        $this->assertEquals(0, $batchA->quantity);
        $this->assertEquals('depleted', $batchA->status);

        $this->assertEquals(15, $batchB->quantity);
        $this->assertEquals('active', $batchB->status);

        // Check stock movement ledger
        $movements = StockMovement::where('medicine_id', $this->medicine->id)->get();
        $this->assertCount(2, $movements);
        $this->assertEquals(-10, $movements->where('batch_id', $batchA->id)->first()->quantity);
        $this->assertEquals(-5, $movements->where('batch_id', $batchB->id)->first()->quantity);
    }

    public function test_expired_batches_are_not_sold(): void
    {
        // Expired batch: expired 5 days ago
        $expiredBatch = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-EXPIRED',
            'quantity' => 15,
            'purchase_price' => 20.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->subDays(5)->toDateString(),
            'status' => 'active',
        ]);

        // Valid batch: expires in 6 months
        $validBatch = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-VALID',
            'quantity' => 10,
            'purchase_price' => 25.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-002',
            'sale_date' => now(),
            'subtotal' => 250.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => 250.00,
            'amount_paid' => 250.00,
            'change_due' => 0.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sold_by' => $this->user->id,
        ]);

        $inventoryService = app(InventoryService::class);

        // Deduct 5 units
        $items = $inventoryService->deductStockFEFO($sale, $this->medicine->id, 5, 50.00, 0, $this->user->id);

        $expiredBatch->refresh();
        $validBatch->refresh();

        // Expired batch should remain untouched
        $this->assertEquals(15, $expiredBatch->quantity);
        // Valid batch should be decremented
        $this->assertEquals(5, $validBatch->quantity);
    }

    public function test_cannot_oversell_available_non_expired_stock(): void
    {
        MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-SMALL',
            'quantity' => 5,
            'purchase_price' => 25.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-003',
            'sale_date' => now(),
            'subtotal' => 500.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => 500.00,
            'amount_paid' => 500.00,
            'change_due' => 0.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sold_by' => $this->user->id,
        ]);

        $inventoryService = app(InventoryService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient non-expired stock for 'Amoxicillin 500mg'");

        // Request 10 units when only 5 are available
        $inventoryService->deductStockFEFO($sale, $this->medicine->id, 10, 50.00, 0, $this->user->id);
    }

    public function test_stock_adjustment_creates_traceable_movement_ledger(): void
    {
        $batch = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'supplier_id' => $this->supplier->id,
            'batch_number' => 'BATCH-ADJ',
            'quantity' => 20,
            'purchase_price' => 25.00,
            'selling_price' => 50.00,
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
        ]);

        $inventoryService = app(InventoryService::class);

        // Adjust -3 due to damaged bottles
        $adjustment = $inventoryService->adjustStock($batch->id, -3, 'Damaged stock', 'Broken seals during shelf restocking', $this->user->id);

        $batch->refresh();
        $this->assertEquals(17, $batch->quantity);
        $this->assertEquals(-3, $adjustment->adjustment_quantity);
        $this->assertEquals(17, $adjustment->new_quantity);

        $movement = StockMovement::where('batch_id', $batch->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-3, $movement->quantity);
        $this->assertEquals(StockMovement::TYPE_ADJUSTMENT_OUT, $movement->type);
    }
}
