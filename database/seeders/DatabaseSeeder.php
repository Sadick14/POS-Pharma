<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        Setting::set('pharmacy_name', 'HealthCare Plus Pharmacy', 'general');
        Setting::set('pharmacy_address', '14 Independence Avenue, Accra, Ghana', 'general');
        Setting::set('pharmacy_phone', '+233 24 123 4567', 'general');
        Setting::set('pharmacy_email', 'info@healthcareplus.com', 'general');
        Setting::set('currency_symbol', 'GHS', 'general');
        Setting::set('currency_code', 'GHS', 'general');
        Setting::set('tax_percentage', 0, 'financial', 'float');
        Setting::set('invoice_prefix', 'INV-', 'pos');
        Setting::set('low_stock_threshold', 20, 'inventory', 'integer');

        // 2. Users (All 6 Roles)
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@pharmacy.com',
            'phone' => '+233 20 000 0001',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $pharmacist = User::create([
            'name' => 'Dr. Kwame Mensah (Pharm)',
            'email' => 'pharmacist@pharmacy.com',
            'phone' => '+233 20 000 0002',
            'role' => User::ROLE_PHARMACIST,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $manager = User::create([
            'name' => 'Abena Osei (Manager)',
            'email' => 'manager@pharmacy.com',
            'phone' => '+233 20 000 0003',
            'role' => User::ROLE_MANAGER,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $cashier = User::create([
            'name' => 'Kofi Boateng (Cashier)',
            'email' => 'cashier@pharmacy.com',
            'phone' => '+233 20 000 0004',
            'role' => User::ROLE_CASHIER,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $inventoryOfficer = User::create([
            'name' => 'Esi Arthur (Inventory Officer)',
            'email' => 'inventory@pharmacy.com',
            'phone' => '+233 20 000 0005',
            'role' => User::ROLE_INVENTORY_OFFICER,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $auditor = User::create([
            'name' => 'Yaw Frimpong (Auditor)',
            'email' => 'auditor@pharmacy.com',
            'phone' => '+233 20 000 0006',
            'role' => User::ROLE_AUDITOR,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        // 3. Categories
        $categoriesData = [
            ['name' => 'Antibiotics', 'description' => 'Medicines that inhibit the growth of or destroy microorganisms.'],
            ['name' => 'Analgesics & Pain Relief', 'description' => 'Painkillers and anti-inflammatory medications.'],
            ['name' => 'Antimalarials', 'description' => 'Medications designed to prevent or treat malaria.'],
            ['name' => 'Antihistamines & Allergy', 'description' => 'Drugs used to treat allergies, cold and hives.'],
            ['name' => 'Vitamins & Dietary Supplements', 'description' => 'Nutritional and immunity boosting supplements.'],
            ['name' => 'Cardiovascular & Hypertensive', 'description' => 'Medications for blood pressure, cholesterol, and heart conditions.'],
            ['name' => 'Diabetes & Endocrine', 'description' => 'Oral hypoglycemics, insulin, and hormone regulators.'],
            ['name' => 'Gastrointestinal', 'description' => 'Antacids, PPIs, antiemetics, and laxatives.'],
            ['name' => 'Respiratory & Cough', 'description' => 'Bronchodilators, cough syrups, and inhalers.'],
            ['name' => 'Dermatology & Topicals', 'description' => 'Creams, ointments, and topical antiseptics.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $categories[$cData['name']] = Category::create([
                'name' => $cData['name'],
                'slug' => Str::slug($cData['name']),
                'description' => $cData['description'],
            ]);
        }

        // 4. Suppliers
        $suppliersData = [
            [
                'name' => 'Ernest Chemists Ltd',
                'contact_person' => 'Ernest Bediako',
                'phone' => '+233 30 222 1445',
                'email' => 'orders@ernestchemists.com',
                'address' => 'Plot 8, Dzorwulu Commercial Area, Accra',
                'registration_number' => 'FDA/GH/SUP-0104',
                'status' => 'active',
            ],
            [
                'name' => 'Tobinco Pharmaceuticals Ltd',
                'contact_person' => 'Samuel Amo Tobin',
                'phone' => '+233 30 281 3156',
                'email' => 'sales@tobinco.com',
                'address' => 'Kotobabi, High Street, Accra',
                'registration_number' => 'FDA/GH/SUP-0219',
                'status' => 'active',
            ],
            [
                'name' => 'Kinapharma Limited',
                'contact_person' => 'Kofi Nsiah-Poku',
                'phone' => '+233 30 266 5789',
                'email' => 'distribution@kinapharma.com',
                'address' => 'Industrial Area, North Kaneshie, Accra',
                'registration_number' => 'FDA/GH/SUP-0344',
                'status' => 'active',
            ],
            [
                'name' => 'Letap Pharmaceuticals Ltd',
                'contact_person' => 'D. Patel',
                'phone' => '+233 30 222 8991',
                'email' => 'info@letappharma.com',
                'address' => 'Graphic Road, South Industrial Area, Accra',
                'registration_number' => 'FDA/GH/SUP-0401',
                'status' => 'active',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sData) {
            $suppliers[] = Supplier::create($sData);
        }

        // 5. Customers
        $customersData = [
            ['name' => 'Kwesi Appiah', 'phone' => '+233 24 991 1223', 'email' => 'kwesi.appiah@gmail.com', 'address' => 'East Legon, Accra'],
            ['name' => 'Grace Ansah', 'phone' => '+233 50 334 4556', 'email' => 'grace.ansah@yahoo.com', 'address' => 'Airport Residential, Accra'],
            ['name' => 'Emmanuel Osei', 'phone' => '+233 27 778 8990', 'email' => 'eosei@hotmail.com', 'address' => 'Tema Community 6'],
            ['name' => 'Akosua Darko', 'phone' => '+233 26 445 5667', 'email' => 'akosuadarko@gmail.com', 'address' => 'Spintex Road, Accra'],
        ];

        $customers = [];
        foreach ($customersData as $custData) {
            $customers[] = Customer::create($custData);
        }

        // 6. Medicines & Batches Data
        $medicinesList = [
            [
                'category' => 'Analgesics & Pain Relief',
                'name' => 'Paracetamol 500mg Tablets',
                'generic_name' => 'Paracetamol / Acetaminophen',
                'brand_name' => 'Panadol Extra',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'Box (100 tabs)',
                'barcode' => '890123450001',
                'manufacturer' => 'GSK Ghana',
                'reorder_level' => 25,
                'selling_price' => 35.00,
                'batches' => [
                    ['batch_number' => 'PARA-2026-01', 'qty' => 45, 'cost' => 22.00, 'sell' => 35.00, 'exp' => now()->addMonths(6)->toDateString()],
                    ['batch_number' => 'PARA-2026-02', 'qty' => 80, 'cost' => 22.50, 'sell' => 35.00, 'exp' => now()->addMonths(18)->toDateString()],
                ],
            ],
            [
                'category' => 'Analgesics & Pain Relief',
                'name' => 'Ibuprofen 400mg Tablets',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Brufen',
                'dosage_form' => 'Tablet',
                'strength' => '400mg',
                'unit' => 'Box (50 tabs)',
                'barcode' => '890123450002',
                'manufacturer' => 'Abbott / Kinapharma',
                'reorder_level' => 20,
                'selling_price' => 28.00,
                'batches' => [
                    ['batch_number' => 'IBU-2026-01', 'qty' => 30, 'cost' => 17.50, 'sell' => 28.00, 'exp' => now()->addMonths(14)->toDateString()],
                ],
            ],
            [
                'category' => 'Antibiotics',
                'name' => 'Amoxicillin 500mg Capsules',
                'generic_name' => 'Amoxicillin Trihydrate',
                'brand_name' => 'Amoxil',
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'unit' => 'Box (100 caps)',
                'barcode' => '890123450003',
                'manufacturer' => 'Ernest Chemists Ltd',
                'reorder_level' => 30,
                'selling_price' => 65.00,
                'batches' => [
                    // Earliest batch expiring in 25 days (Trigger < 30 days alert)
                    ['batch_number' => 'AMX-2026-01', 'qty' => 15, 'cost' => 42.00, 'sell' => 65.00, 'exp' => now()->addDays(22)->toDateString()],
                    // Fresh batch
                    ['batch_number' => 'AMX-2026-02', 'qty' => 60, 'cost' => 44.00, 'sell' => 65.00, 'exp' => now()->addMonths(20)->toDateString()],
                ],
            ],
            [
                'category' => 'Antibiotics',
                'name' => 'Azithromycin 500mg Tablets',
                'generic_name' => 'Azithromycin',
                'brand_name' => 'Zithromax',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'Pack (3 tabs)',
                'barcode' => '890123450004',
                'manufacturer' => 'Pfizer / Tobinco',
                'reorder_level' => 15,
                'selling_price' => 45.00,
                'batches' => [
                    ['batch_number' => 'AZT-2026-01', 'qty' => 25, 'cost' => 28.00, 'sell' => 45.00, 'exp' => now()->addMonths(12)->toDateString()],
                ],
            ],
            [
                'category' => 'Antimalarials',
                'name' => 'Artemether + Lumefantrine (Coartem) 20/120mg',
                'generic_name' => 'Artemether / Lumefantrine',
                'brand_name' => 'Coartem 20/120',
                'dosage_form' => 'Tablet',
                'strength' => '20/120mg',
                'unit' => 'Pack (24 tabs)',
                'barcode' => '890123450005',
                'manufacturer' => 'Novartis Pharma',
                'reorder_level' => 40,
                'selling_price' => 38.00,
                'batches' => [
                    ['batch_number' => 'CRT-2026-01', 'qty' => 55, 'cost' => 24.00, 'sell' => 38.00, 'exp' => now()->addMonths(15)->toDateString()],
                    ['batch_number' => 'CRT-2026-02', 'qty' => 90, 'cost' => 24.00, 'sell' => 38.00, 'exp' => now()->addMonths(24)->toDateString()],
                ],
            ],
            [
                'category' => 'Antimalarials',
                'name' => 'Dihydroartemisinin + Piperaquine (Artequick)',
                'generic_name' => 'Dihydroartemisinin / Piperaquine',
                'brand_name' => 'Artequick',
                'dosage_form' => 'Tablet',
                'strength' => '40/320mg',
                'unit' => 'Pack (8 tabs)',
                'barcode' => '890123450006',
                'manufacturer' => 'Artepharm Co.',
                'reorder_level' => 20,
                'selling_price' => 42.00,
                'batches' => [
                    ['batch_number' => 'ART-2026-01', 'qty' => 35, 'cost' => 26.00, 'sell' => 42.00, 'exp' => now()->addMonths(16)->toDateString()],
                ],
            ],
            [
                'category' => 'Antihistamines & Allergy',
                'name' => 'Cetirizine 10mg Tablets',
                'generic_name' => 'Cetirizine Hydrochloride',
                'brand_name' => 'Zyrtec',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'unit' => 'Box (30 tabs)',
                'barcode' => '890123450007',
                'manufacturer' => 'GSK / Letap',
                'reorder_level' => 15,
                'selling_price' => 22.00,
                'batches' => [
                    ['batch_number' => 'CET-2026-01', 'qty' => 28, 'cost' => 12.00, 'sell' => 22.00, 'exp' => now()->addMonths(10)->toDateString()],
                ],
            ],
            [
                'category' => 'Antihistamines & Allergy',
                'name' => 'Loratadine 10mg Tablets',
                'generic_name' => 'Loratadine',
                'brand_name' => 'Claritin',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'unit' => 'Box (20 tabs)',
                'barcode' => '890123450008',
                'manufacturer' => 'Bayer Healthcare',
                'reorder_level' => 15,
                'selling_price' => 30.00,
                'batches' => [
                    // Low stock item (qty 6 <= reorder level 15)
                    ['batch_number' => 'LOR-2026-01', 'qty' => 6, 'cost' => 18.00, 'sell' => 30.00, 'exp' => now()->addMonths(8)->toDateString()],
                ],
            ],
            [
                'category' => 'Vitamins & Dietary Supplements',
                'name' => 'Vitamin C 1000mg Effervescent',
                'generic_name' => 'Ascorbic Acid + Zinc',
                'brand_name' => 'Redoxon',
                'dosage_form' => 'Effervescent Tablet',
                'strength' => '1000mg',
                'unit' => 'Tube (15 tabs)',
                'barcode' => '890123450009',
                'manufacturer' => 'Bayer',
                'reorder_level' => 20,
                'selling_price' => 50.00,
                'batches' => [
                    ['batch_number' => 'VITC-2026-01', 'qty' => 40, 'cost' => 32.00, 'sell' => 50.00, 'exp' => now()->addMonths(18)->toDateString()],
                ],
            ],
            [
                'category' => 'Vitamins & Dietary Supplements',
                'name' => 'Multivitamin & Minerals Syrup 200ml',
                'generic_name' => 'Multivitamins with Iron & Lysine',
                'brand_name' => 'Vitaglobin',
                'dosage_form' => 'Syrup',
                'strength' => '200ml',
                'unit' => 'Bottle',
                'barcode' => '890123450010',
                'manufacturer' => 'Vitabiotics UK',
                'reorder_level' => 15,
                'selling_price' => 48.00,
                'batches' => [
                    // Expiring in 45 days (<60 days alert)
                    ['batch_number' => 'MV-2026-01', 'qty' => 12, 'cost' => 30.00, 'sell' => 48.00, 'exp' => now()->addDays(45)->toDateString()],
                ],
            ],
            [
                'category' => 'Cardiovascular & Hypertensive',
                'name' => 'Amlodipine 5mg Tablets',
                'generic_name' => 'Amlodipine Besylate',
                'brand_name' => 'Norvasc',
                'dosage_form' => 'Tablet',
                'strength' => '5mg',
                'unit' => 'Box (30 tabs)',
                'barcode' => '890123450011',
                'manufacturer' => 'Pfizer',
                'reorder_level' => 20,
                'selling_price' => 40.00,
                'batches' => [
                    ['batch_number' => 'AML-2026-01', 'qty' => 35, 'cost' => 25.00, 'sell' => 40.00, 'exp' => now()->addMonths(14)->toDateString()],
                ],
            ],
            [
                'category' => 'Cardiovascular & Hypertensive',
                'name' => 'Losartan Potassium 50mg Tablets',
                'generic_name' => 'Losartan Potassium',
                'brand_name' => 'Cozaar',
                'dosage_form' => 'Tablet',
                'strength' => '50mg',
                'unit' => 'Box (28 tabs)',
                'barcode' => '890123450012',
                'manufacturer' => 'Merck Sharp & Dohme',
                'reorder_level' => 20,
                'selling_price' => 55.00,
                'batches' => [
                    // Low stock item (qty 5 <= reorder level 20)
                    ['batch_number' => 'LOS-2026-01', 'qty' => 5, 'cost' => 35.00, 'sell' => 55.00, 'exp' => now()->addMonths(12)->toDateString()],
                ],
            ],
            [
                'category' => 'Diabetes & Endocrine',
                'name' => 'Metformin HCl 500mg Tablets',
                'generic_name' => 'Metformin Hydrochloride',
                'brand_name' => 'Glucophage',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit' => 'Box (100 tabs)',
                'barcode' => '890123450013',
                'manufacturer' => 'Merck Santé',
                'reorder_level' => 30,
                'selling_price' => 42.00,
                'batches' => [
                    ['batch_number' => 'MET-2026-01', 'qty' => 50, 'cost' => 26.00, 'sell' => 42.00, 'exp' => now()->addMonths(16)->toDateString()],
                ],
            ],
            [
                'category' => 'Gastrointestinal',
                'name' => 'Omeprazole 20mg Capsules',
                'generic_name' => 'Omeprazole',
                'brand_name' => 'Losec / Omez',
                'dosage_form' => 'Capsule',
                'strength' => '20mg',
                'unit' => 'Box (30 caps)',
                'barcode' => '890123450014',
                'manufacturer' => 'Dr. Reddy\'s Lab',
                'reorder_level' => 20,
                'selling_price' => 32.00,
                'batches' => [
                    ['batch_number' => 'OMP-2026-01', 'qty' => 40, 'cost' => 19.00, 'sell' => 32.00, 'exp' => now()->addMonths(11)->toDateString()],
                ],
            ],
            [
                'category' => 'Gastrointestinal',
                'name' => 'Antacid Magnesium Trisilicate Suspension 200ml',
                'generic_name' => 'Magnesium Trisilicate & Aluminium Hydroxide',
                'brand_name' => 'Gelusil / Gestid',
                'dosage_form' => 'Suspension',
                'strength' => '200ml',
                'unit' => 'Bottle',
                'barcode' => '890123450015',
                'manufacturer' => 'Ernest Chemists Ltd',
                'reorder_level' => 15,
                'selling_price' => 24.00,
                'batches' => [
                    ['batch_number' => 'GEL-2026-01', 'qty' => 30, 'cost' => 14.00, 'sell' => 24.00, 'exp' => now()->addMonths(15)->toDateString()],
                ],
            ],
            [
                'category' => 'Respiratory & Cough',
                'name' => 'Salbutamol Inhaler 100mcg',
                'generic_name' => 'Salbutamol / Albuterol',
                'brand_name' => 'Ventolin Evohaler',
                'dosage_form' => 'Inhaler',
                'strength' => '100mcg (200 doses)',
                'unit' => 'Canister',
                'barcode' => '890123450016',
                'manufacturer' => 'GSK UK',
                'reorder_level' => 10,
                'selling_price' => 60.00,
                'batches' => [
                    ['batch_number' => 'VENT-2026-01', 'qty' => 18, 'cost' => 40.00, 'sell' => 60.00, 'exp' => now()->addMonths(22)->toDateString()],
                ],
            ],
            [
                'category' => 'Dermatology & Topicals',
                'name' => 'Hydrocortisone 1% Cream 15g',
                'generic_name' => 'Hydrocortisone Acetate',
                'brand_name' => 'Dioderm',
                'dosage_form' => 'Cream',
                'strength' => '1% (15g)',
                'unit' => 'Tube',
                'barcode' => '890123450017',
                'manufacturer' => 'Letap Pharmaceuticals',
                'reorder_level' => 15,
                'selling_price' => 18.00,
                'batches' => [
                    // Expired batch example for testing non-saleable protection and disposal alerts!
                    ['batch_number' => 'HYD-2025-EX', 'qty' => 8, 'cost' => 10.00, 'sell' => 18.00, 'exp' => now()->subMonths(2)->toDateString()],
                    ['batch_number' => 'HYD-2026-01', 'qty' => 22, 'cost' => 10.50, 'sell' => 18.00, 'exp' => now()->addMonths(14)->toDateString()],
                ],
            ],
        ];

        foreach ($medicinesList as $mIdx => $mItem) {
            $cat = $categories[$mItem['category']] ?? $categories['Analgesics & Pain Relief'];
            $supplier = $suppliers[$mIdx % count($suppliers)];

            $med = Medicine::create([
                'category_id' => $cat->id,
                'name' => $mItem['name'],
                'generic_name' => $mItem['generic_name'],
                'brand_name' => $mItem['brand_name'],
                'dosage_form' => $mItem['dosage_form'],
                'strength' => $mItem['strength'],
                'unit' => $mItem['unit'],
                'barcode' => $mItem['barcode'],
                'manufacturer' => $mItem['manufacturer'],
                'description' => "Standard pharmaceutical formulation by {$mItem['manufacturer']}.",
                'reorder_level' => $mItem['reorder_level'],
                'selling_price' => $mItem['selling_price'],
                'status' => 'active',
            ]);

            foreach ($mItem['batches'] as $bInfo) {
                $batch = MedicineBatch::create([
                    'medicine_id' => $med->id,
                    'supplier_id' => $supplier->id,
                    'batch_number' => $bInfo['batch_number'],
                    'quantity' => $bInfo['qty'],
                    'purchase_price' => $bInfo['cost'],
                    'selling_price' => $bInfo['sell'],
                    'manufactured_date' => Carbon::parse($bInfo['exp'])->subYears(2)->toDateString(),
                    'expiry_date' => $bInfo['exp'],
                    'received_date' => now()->subDays(15)->toDateString(),
                    'status' => 'active',
                ]);

                // Create initial stock movement ledger entry
                StockMovement::create([
                    'medicine_id' => $med->id,
                    'batch_id' => $batch->id,
                    'type' => StockMovement::TYPE_PURCHASE_IN,
                    'quantity' => $bInfo['qty'],
                    'reference_type' => Supplier::class,
                    'reference_id' => $supplier->id,
                    'description' => "Initial Stock Inward from {$supplier->name} (Batch {$batch->batch_number})",
                    'created_by' => $inventoryOfficer->id,
                ]);
            }
        }

        // 7. Seed Sample Completed Sales
        $sampleSales = [
            [
                'customer_id' => $customers[0]->id,
                'invoice_number' => 'INV-'.date('Ymd').'-0001',
                'sale_date' => now()->subHours(4),
                'payment_method' => 'cash',
                'amount_paid' => 100.00,
                'sold_by' => $cashier->id,
                'items' => [
                    ['barcode' => '890123450001', 'qty' => 2], // Paracetamol
                    ['barcode' => '890123450007', 'qty' => 1], // Cetirizine
                ],
            ],
            [
                'customer_id' => $customers[1]->id,
                'invoice_number' => 'INV-'.date('Ymd').'-0002',
                'sale_date' => now()->subHours(2),
                'payment_method' => 'mobile_money',
                'amount_paid' => 140.00,
                'sold_by' => $pharmacist->id,
                'items' => [
                    ['barcode' => '890123450003', 'qty' => 1], // Amoxicillin (will deduct earliest non-expired batch)
                    ['barcode' => '890123450005', 'qty' => 2], // Coartem
                ],
            ],
            [
                'customer_id' => null, // Walk-in customer
                'invoice_number' => 'INV-'.date('Ymd').'-0003',
                'sale_date' => now()->subMinutes(45),
                'payment_method' => 'card',
                'amount_paid' => 50.00,
                'sold_by' => $cashier->id,
                'items' => [
                    ['barcode' => '890123450009', 'qty' => 1], // Vitamin C
                ],
            ],
        ];

        foreach ($sampleSales as $sRecord) {
            $totalSubtotal = 0;
            $saleItemsToInsert = [];

            foreach ($sRecord['items'] as $it) {
                $med = Medicine::where('barcode', $it['barcode'])->first();
                if ($med) {
                    $earliestBatch = $med->activeBatches()->first();
                    if ($earliestBatch) {
                        $qty = min($it['qty'], $earliestBatch->quantity);
                        $lineSubtotal = $qty * $med->selling_price;
                        $totalSubtotal += $lineSubtotal;

                        $earliestBatch->quantity -= $qty;
                        $earliestBatch->save();

                        $saleItemsToInsert[] = [
                            'medicine_id' => $med->id,
                            'batch_id' => $earliestBatch->id,
                            'quantity' => $qty,
                            'unit_price' => $med->selling_price,
                            'purchase_price' => $earliestBatch->purchase_price,
                            'discount' => 0.00,
                            'subtotal' => $lineSubtotal,
                        ];
                    }
                }
            }

            $sale = Sale::create([
                'customer_id' => $sRecord['customer_id'],
                'invoice_number' => $sRecord['invoice_number'],
                'sale_date' => $sRecord['sale_date'],
                'subtotal' => $totalSubtotal,
                'discount' => 0.00,
                'tax' => 0.00,
                'total' => $totalSubtotal,
                'amount_paid' => $sRecord['amount_paid'],
                'change_due' => max(0, $sRecord['amount_paid'] - $totalSubtotal),
                'payment_method' => $sRecord['payment_method'],
                'payment_status' => 'paid',
                'sold_by' => $sRecord['sold_by'],
            ]);

            foreach ($saleItemsToInsert as $si) {
                $si['sale_id'] = $sale->id;
                SaleItem::create($si);

                StockMovement::create([
                    'medicine_id' => $si['medicine_id'],
                    'batch_id' => $si['batch_id'],
                    'type' => StockMovement::TYPE_SALE_OUT,
                    'quantity' => -$si['quantity'],
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'description' => "POS Sale #{$sale->invoice_number}",
                    'created_by' => $sale->sold_by,
                ]);
            }
        }

        // 8. Seed Initial Audit Log
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'system_initialized',
            'module' => 'System',
            'record_id' => null,
            'description' => 'System database initialized and seeded with comprehensive pharmacy stock catalog and initial batches.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder Script',
        ]);
    }
}
