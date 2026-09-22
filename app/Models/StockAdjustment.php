<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'batch_id',
        'previous_quantity',
        'adjustment_quantity',
        'new_quantity',
        'reason',
        'notes',
        'adjusted_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_quantity' => 'integer',
            'adjustment_quantity' => 'integer',
            'new_quantity' => 'integer',
        ];
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function adjuster()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
