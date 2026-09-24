<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $pharmacist;

    protected Medicine $medicine;

    protected MedicineBatch $batch;

    protected Sale $sale;

    protected SaleItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pharmacist = User::create([
            'name' => 'Dr. Kwame',
            'email' => 'kwame@test.com',
            'role' => User::ROLE_PHARMACIST,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $cat = Category::create(['name' => 'Antimalarials', 'slug' => 'antimalarials']);

        $this->medicine = Medicine::create([
            'category_id' => $cat->id,
            'name' => 'Coartem 20/120mg',
            'unit' => 'Pack',
            'reorder_level' => 10,
            'selling_price' => 40.00,
            'status' => 'active',
        ]);

        $this->batch = MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'batch_number' => 'CRT-99',
            'quantity' => 15,
            'purchase_price' => 25.00,
            'selling_price' => 40.00,
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $this->sale = Sale::create([
            'invoice_number' => 'INV-TEST-RET-01',
            'sale_date' => now(),
            'subtotal' => 80.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => 80.00,
            'amount_paid' => 80.00,
            'change_due' => 0.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sold_by' => $this->pharmacist->id,
        ]);

        $this->saleItem = SaleItem::create([
            'sale_id' => $this->sale->id,
            'medicine_id' => $this->medicine->id,
            'batch_id' => $this->batch->id,
            'quantity' => 2,
            'unit_price' => 40.00,
            'purchase_price' => 25.00,
            'discount' => 0.00,
            'subtotal' => 80.00,
        ]);
    }

    public function test_resalable_sales_return_restocks_batch_and_logs_movement(): void
    {
        $response = $this->actingAs($this->pharmacist)->post(route('returns.store', $this->sale), [
            'refund_method' => 'cash',
            'reason' => 'Customer bought wrong dosage form',
            'items' => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'medicine_id' => $this->medicine->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 1,
                    'unit_refund_price' => 40.00,
                    'is_resalable' => 1,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $this->batch->refresh();
        $this->assertEquals(16, $this->batch->quantity); // 15 + 1 returned = 16

        $this->assertDatabaseHas('sales_returns', [
            'sale_id' => $this->sale->id,
            'total_refund' => 40.00,
            'refund_method' => 'cash',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id' => $this->medicine->id,
            'batch_id' => $this->batch->id,
            'type' => StockMovement::TYPE_RETURN_IN,
            'quantity' => 1,
        ]);
    }
}
