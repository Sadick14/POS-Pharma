<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurchaseReceivingTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Supplier $supplier;

    protected Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'role' => User::ROLE_MANAGER,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Tobinco Pharma Ltd',
            'status' => 'active',
        ]);

        $cat = Category::create(['name' => 'Vitamins', 'slug' => 'vitamins']);

        $this->medicine = Medicine::create([
            'category_id' => $cat->id,
            'name' => 'Vitamin C 1000mg',
            'unit' => 'Tube',
            'reorder_level' => 10,
            'selling_price' => 45.00,
            'status' => 'active',
        ]);
    }

    public function test_purchase_inward_creates_batch_and_stock_movement(): void
    {
        $response = $this->actingAs($this->manager)->post(route('purchases.store'), [
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'PUR-INV-999',
            'purchase_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'batch_number' => 'VITC-2026-N1',
                    'expiry_date' => now()->addMonths(18)->toDateString(),
                    'quantity' => 100,
                    'unit_cost' => 30.00,
                    'selling_price' => 48.00,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $purchase = Purchase::where('invoice_number', 'PUR-INV-999')->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(3000.00, $purchase->total);

        $batch = MedicineBatch::where('medicine_id', $this->medicine->id)
            ->where('batch_number', 'VITC-2026-N1')
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals(100, $batch->quantity);
        $this->assertEquals(30.00, $batch->purchase_price);
        $this->assertEquals(48.00, $batch->selling_price);

        $movement = StockMovement::where('medicine_id', $this->medicine->id)
            ->where('batch_id', $batch->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(100, $movement->quantity);
        $this->assertEquals(StockMovement::TYPE_PURCHASE_IN, $movement->type);
    }
}
