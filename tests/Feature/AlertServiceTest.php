<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_alert_service_identifies_low_stock_and_expiry_windows(): void
    {
        $cat = Category::create(['name' => 'General', 'slug' => 'general']);
        $supplier = Supplier::create(['name' => 'Test Supplier', 'status' => 'active']);

        // Medicine 1: Reorder level 20, active batches total 12 -> LOW STOCK
        $med1 = Medicine::create([
            'category_id' => $cat->id,
            'name' => 'Low Stock Med',
            'unit' => 'Box',
            'reorder_level' => 20,
            'selling_price' => 25.00,
            'status' => 'active',
        ]);

        MedicineBatch::create([
            'medicine_id' => $med1->id,
            'supplier_id' => $supplier->id,
            'batch_number' => 'B-LOW-01',
            'quantity' => 12,
            'purchase_price' => 15.00,
            'selling_price' => 25.00,
            'expiry_date' => now()->addMonths(8)->toDateString(),
            'status' => 'active',
        ]);

        // Medicine 2: Has an expired batch and a batch expiring in 20 days
        $med2 = Medicine::create([
            'category_id' => $cat->id,
            'name' => 'Expiring Med',
            'unit' => 'Bottle',
            'reorder_level' => 5,
            'selling_price' => 30.00,
            'status' => 'active',
        ]);

        // Expired
        MedicineBatch::create([
            'medicine_id' => $med2->id,
            'supplier_id' => $supplier->id,
            'batch_number' => 'B-EXP',
            'quantity' => 5,
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'expiry_date' => now()->subDays(10)->toDateString(),
            'status' => 'active',
        ]);

        // Expiring in 20 days (< 30 days alert)
        MedicineBatch::create([
            'medicine_id' => $med2->id,
            'supplier_id' => $supplier->id,
            'batch_number' => 'B-SOON-20',
            'quantity' => 8,
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'expiry_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
        ]);

        $alertService = app(AlertService::class);

        $lowStock = $alertService->getLowStockMedicines();
        $this->assertCount(1, $lowStock);
        $this->assertEquals($med1->id, $lowStock->first()->id);

        $expiryCounts = $alertService->getExpiryCounts();
        $this->assertEquals(1, $expiryCounts['expired']);
        $this->assertEquals(1, $expiryCounts['expiring_30']);

        $expiredBatches = $alertService->getExpiredBatches();
        $this->assertCount(1, $expiredBatches);
        $this->assertEquals('B-EXP', $expiredBatches->first()->batch_number);

        $expiring30 = $alertService->getExpiringBatches(30);
        $this->assertCount(1, $expiring30);
        $this->assertEquals('B-SOON-20', $expiring30->first()->batch_number);
    }
}
