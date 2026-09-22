<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Medicine $paracetamol;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::create([
            'name' => 'Kofi Cashier',
            'email' => 'cashier@test.com',
            'role' => User::ROLE_CASHIER,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $cat = Category::create(['name' => 'Analgesics', 'slug' => 'analgesics']);

        $this->paracetamol = Medicine::create([
            'category_id' => $cat->id,
            'name' => 'Paracetamol 500mg',
            'unit' => 'Box',
            'barcode' => '89000111',
            'reorder_level' => 10,
            'selling_price' => 30.00,
            'status' => 'active',
        ]);

        MedicineBatch::create([
            'medicine_id' => $this->paracetamol->id,
            'batch_number' => 'PARA-01',
            'quantity' => 50,
            'purchase_price' => 18.00,
            'selling_price' => 30.00,
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'name' => 'Grace Ansah',
            'phone' => '0241234567',
        ]);
    }

    public function test_pos_checkout_succeeds_and_creates_records(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.checkout'), [
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'medicine_id' => $this->paracetamol->id,
                    'quantity' => 3,
                    'unit_price' => 30.00,
                    'discount' => 0.00,
                ]
            ],
            'payment_method' => 'cash',
            'amount_paid' => 100.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 90.00,
            'change_due' => 10.00,
        ]);

        $this->assertDatabaseHas('sales', [
            'customer_id' => $this->customer->id,
            'total' => 90.00,
            'amount_paid' => 100.00,
            'change_due' => 10.00,
            'sold_by' => $this->cashier->id,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'medicine_id' => $this->paracetamol->id,
            'quantity' => 3,
            'unit_price' => 30.00,
            'subtotal' => 90.00,
        ]);

        $batch = MedicineBatch::where('medicine_id', $this->paracetamol->id)->first();
        $this->assertEquals(47, $batch->quantity);
    }

    public function test_cashier_receives_receipt(): void
    {
        $sale = Sale::create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2026-0099',
            'sale_date' => now(),
            'subtotal' => 60.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total' => 60.00,
            'amount_paid' => 100.00,
            'change_due' => 40.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sold_by' => $this->cashier->id,
        ]);

        $response = $this->actingAs($this->cashier)->get(route('pos.receipt', $sale));
        $response->assertStatus(200);
        $response->assertSee('INV-2026-0099');
        $response->assertSee('Grace Ansah');
    }
}
