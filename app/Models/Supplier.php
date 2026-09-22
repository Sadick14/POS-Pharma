<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'registration_number',
        'status',
        'notes',
    ];

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
