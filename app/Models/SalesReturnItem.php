<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sale_item_id',
        'medicine_id',
        'batch_id',
        'quantity',
        'unit_refund_price',
        'subtotal',
        'is_resalable',
        'condition_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_refund_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'is_resalable' => 'boolean',
        ];
    }

    public function return()
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class);
    }
}
