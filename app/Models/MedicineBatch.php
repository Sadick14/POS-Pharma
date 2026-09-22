<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'supplier_id',
        'batch_number',
        'quantity',
        'purchase_price',
        'selling_price',
        'manufactured_date',
        'expiry_date',
        'received_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'manufactured_date' => 'date',
            'expiry_date' => 'date',
            'received_date' => 'date',
        ];
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'batch_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'batch_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('status', 'active');
    }

    public function scopeFefo($query)
    {
        return $query->available()->orderBy('expiry_date', 'asc');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString())
            ->where('quantity', '>', 0);
    }

    public function scopeExpiringWithin($query, int $days)
    {
        $today = now()->toDateString();
        $future = now()->addDays($days)->toDateString();

        return $query->where('quantity', '>', 0)
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $future);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date->lt(now()->startOfDay());
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }
}
