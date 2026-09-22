<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deducts medicine stock using FEFO (First Expiry, First Out).
     *
     * @throws Exception If stock is insufficient or medicine has no active non-expired stock.
     */
    public function deductStockFEFO(Sale $sale, int $medicineId, int $requestedQty, ?float $unitPrice = null, float $discount = 0.00, ?int $userId = null): array
    {
        $medicine = Medicine::findOrFail($medicineId);

        // Fetch available, active, non-expired batches ordered by expiry date ASC
        $batches = MedicineBatch::where('medicine_id', $medicineId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = $batches->sum('quantity');

        if ($totalAvailable < $requestedQty) {
            throw new Exception("Insufficient non-expired stock for '{$medicine->name}'. Requested: {$requestedQty}, Available: {$totalAvailable}.");
        }

        $remainingToDeduct = $requestedQty;
        $createdSaleItems = [];

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductFromBatch = min($remainingToDeduct, $batch->quantity);
            $batch->quantity -= $deductFromBatch;
            if ($batch->quantity === 0) {
                $batch->status = 'depleted';
            }
            $batch->save();

            $price = $unitPrice ?? ($batch->selling_price > 0 ? $batch->selling_price : $medicine->selling_price);
            $lineDiscount = ($discount / $requestedQty) * $deductFromBatch;
            $lineSubtotal = ($price * $deductFromBatch) - $lineDiscount;

            // Create Sale Item linked to this specific batch
            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'medicine_id' => $medicineId,
                'batch_id' => $batch->id,
                'quantity' => $deductFromBatch,
                'unit_price' => $price,
                'purchase_price' => $batch->purchase_price,
                'discount' => $lineDiscount,
                'subtotal' => $lineSubtotal,
            ]);

            // Record immutable stock movement
            StockMovement::create([
                'medicine_id' => $medicineId,
                'batch_id' => $batch->id,
                'type' => StockMovement::TYPE_SALE_OUT,
                'quantity' => -$deductFromBatch,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'description' => "POS Sale #{$sale->invoice_number} (FEFO Batch: {$batch->batch_number}, Exp: {$batch->expiry_date->format('Y-m-d')})",
                'created_by' => $userId ?? $sale->sold_by,
            ]);

            $createdSaleItems[] = $saleItem;
            $remainingToDeduct -= $deductFromBatch;
        }

        return $createdSaleItems;
    }

    /**
     * Receives new stock from a Purchase Order into batches and ledger.
     */
    public function receivePurchaseStock(Purchase $purchase, array $itemsData, ?int $userId = null): void
    {
        DB::transaction(function () use ($purchase, $itemsData, $userId) {
            foreach ($itemsData as $item) {
                $medicine = Medicine::findOrFail($item['medicine_id']);
                
                // Find or create batch
                $batch = MedicineBatch::where('medicine_id', $item['medicine_id'])
                    ->where('batch_number', $item['batch_number'])
                    ->first();

                if ($batch) {
                    $batch->quantity += (int) $item['quantity'];
                    $batch->purchase_price = $item['unit_cost'];
                    $batch->selling_price = $item['selling_price'];
                    $batch->expiry_date = $item['expiry_date'];
                    if (isset($item['manufactured_date'])) {
                        $batch->manufactured_date = $item['manufactured_date'];
                    }
                    if ($batch->status === 'depleted') {
                        $batch->status = 'active';
                    }
                    $batch->save();
                } else {
                    $batch = MedicineBatch::create([
                        'medicine_id' => $item['medicine_id'],
                        'supplier_id' => $purchase->supplier_id,
                        'batch_number' => $item['batch_number'],
                        'quantity' => (int) $item['quantity'],
                        'purchase_price' => $item['unit_cost'],
                        'selling_price' => $item['selling_price'],
                        'manufactured_date' => $item['manufactured_date'] ?? null,
                        'expiry_date' => $item['expiry_date'],
                        'received_date' => $purchase->purchase_date,
                        'status' => 'active',
                    ]);
                }

                // Update default medicine selling price if set
                if ($item['selling_price'] > 0) {
                    $medicine->update(['selling_price' => $item['selling_price']]);
                }

                $subtotal = $item['quantity'] * $item['unit_cost'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $batch->id,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'selling_price' => $item['selling_price'],
                    'subtotal' => $subtotal,
                ]);

                StockMovement::create([
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $batch->id,
                    'type' => StockMovement::TYPE_PURCHASE_IN,
                    'quantity' => (int) $item['quantity'],
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'description' => "Purchase #{$purchase->invoice_number} (Batch {$batch->batch_number})",
                    'created_by' => $userId ?? $purchase->created_by,
                ]);
            }

            AuditLog::log(
                'purchase_received',
                'Purchases',
                (string) $purchase->id,
                "Received purchase invoice #{$purchase->invoice_number} with " . count($itemsData) . " line item(s)"
            );
        });
    }

    /**
     * Performs a stock adjustment (Damage, Expiry, Discrepancy correction, etc.)
     */
    public function adjustStock(int $batchId, int $adjustmentQty, string $reason, ?string $notes = null, ?int $userId = null): StockAdjustment
    {
        return DB::transaction(function () use ($batchId, $adjustmentQty, $reason, $notes, $userId) {
            $batch = MedicineBatch::with('medicine')->lockForUpdate()->findOrFail($batchId);
            
            $previousQty = $batch->quantity;
            $newQty = $previousQty + $adjustmentQty;

            if ($newQty < 0) {
                throw new Exception("Adjustment would result in negative stock. Current: {$previousQty}, Adjustment: {$adjustmentQty}.");
            }

            $batch->quantity = $newQty;
            if ($newQty === 0) {
                $batch->status = 'depleted';
            } elseif ($batch->status === 'depleted' && $newQty > 0) {
                $batch->status = 'active';
            }
            $batch->save();

            $adjustment = StockAdjustment::create([
                'medicine_id' => $batch->medicine_id,
                'batch_id' => $batch->id,
                'previous_quantity' => $previousQty,
                'adjustment_quantity' => $adjustmentQty,
                'new_quantity' => $newQty,
                'reason' => $reason,
                'notes' => $notes,
                'adjusted_by' => $userId,
            ]);

            StockMovement::create([
                'medicine_id' => $batch->medicine_id,
                'batch_id' => $batch->id,
                'type' => $adjustmentQty >= 0 ? StockMovement::TYPE_ADJUSTMENT_IN : StockMovement::TYPE_ADJUSTMENT_OUT,
                'quantity' => $adjustmentQty,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'description' => "Stock adjustment: {$reason} (" . ($adjustmentQty >= 0 ? "+{$adjustmentQty}" : "{$adjustmentQty}") . ")",
                'created_by' => $userId,
            ]);

            AuditLog::log(
                'stock_adjusted',
                'Inventory',
                (string) $batch->medicine_id,
                "Adjusted batch {$batch->batch_number} ({$batch->medicine->name}): {$previousQty} -> {$newQty}. Reason: {$reason}",
                ['quantity' => $previousQty],
                ['quantity' => $newQty, 'reason' => $reason]
            );

            return $adjustment;
        });
    }

    /**
     * Processes a sales return.
     */
    public function processSalesReturn(Sale $sale, array $itemsData, string $refundMethod, ?string $reason = null, ?int $userId = null): SalesReturn
    {
        return DB::transaction(function () use ($sale, $itemsData, $refundMethod, $reason, $userId) {
            $totalRefund = 0;
            foreach ($itemsData as $item) {
                $totalRefund += ($item['quantity'] * $item['unit_refund_price']);
            }

            $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad((string) (SalesReturn::count() + 1), 4, '0', STR_PAD_LEFT);

            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'return_number' => $returnNumber,
                'total_refund' => $totalRefund,
                'refund_method' => $refundMethod,
                'reason' => $reason,
                'processed_by' => $userId,
            ]);

            foreach ($itemsData as $item) {
                $isResalable = (bool) ($item['is_resalable'] ?? true);
                $subtotal = $item['quantity'] * $item['unit_refund_price'];

                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'sale_item_id' => $item['sale_item_id'] ?? null,
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_refund_price' => $item['unit_refund_price'],
                    'subtotal' => $subtotal,
                    'is_resalable' => $isResalable,
                    'condition_notes' => $item['condition_notes'] ?? null,
                ]);

                if ($isResalable && !empty($item['batch_id'])) {
                    $batch = MedicineBatch::find($item['batch_id']);
                    if ($batch) {
                        $batch->quantity += (int) $item['quantity'];
                        if ($batch->status === 'depleted') {
                            $batch->status = 'active';
                        }
                        $batch->save();

                        StockMovement::create([
                            'medicine_id' => $item['medicine_id'],
                            'batch_id' => $batch->id,
                            'type' => StockMovement::TYPE_RETURN_IN,
                            'quantity' => (int) $item['quantity'],
                            'reference_type' => SalesReturn::class,
                            'reference_id' => $salesReturn->id,
                            'description' => "Restocked from Return #{$returnNumber} (Sale #{$sale->invoice_number})",
                            'created_by' => $userId,
                        ]);
                    }
                } else {
                    StockMovement::create([
                        'medicine_id' => $item['medicine_id'],
                        'batch_id' => $item['batch_id'] ?? null,
                        'type' => StockMovement::TYPE_DISPOSAL_OUT,
                        'quantity' => -(int) $item['quantity'],
                        'reference_type' => SalesReturn::class,
                        'reference_id' => $salesReturn->id,
                        'description' => "Damaged/Unusable return written off from Return #{$returnNumber}",
                        'created_by' => $userId,
                    ]);
                }
            }

            AuditLog::log(
                'sale_return',
                'Sales',
                (string) $salesReturn->id,
                "Processed return #{$returnNumber} for sale #{$sale->invoice_number}. Total refund: " . number_format($totalRefund, 2)
            );

            return $salesReturn;
        });
    }
}
