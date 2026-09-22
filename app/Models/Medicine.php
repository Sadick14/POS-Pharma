<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'generic_name',
        'brand_name',
        'dosage_form',
        'strength',
        'unit',
        'barcode',
        'manufacturer',
        'description',
        'reorder_level',
        'selling_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reorder_level' => 'integer',
            'selling_price' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function activeBatches()
    {
        return $this->hasMany(MedicineBatch::class)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('status', 'active')
            ->orderBy('expiry_date', 'asc');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function getCurrentStockAttribute(): int
    {
        return (int) $this->activeBatches()->sum('quantity');
    }

    public function getTotalStockAllBatchesAttribute(): int
    {
        return (int) $this->batches()->where('quantity', '>', 0)->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->reorder_level;
    }

    public function getEarliestExpiryBatchAttribute()
    {
        return $this->activeBatches()->first();
    }
}
