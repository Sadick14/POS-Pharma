<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PHARMACIST = 'pharmacist';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_INVENTORY_OFFICER = 'inventory_officer';

    public const ROLE_AUDITOR = 'auditor';

    public static array $roles = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_PHARMACIST => 'Pharmacist',
        self::ROLE_MANAGER => 'Pharmacy Manager',
        self::ROLE_CASHIER => 'Cashier',
        self::ROLE_INVENTORY_OFFICER => 'Inventory Officer',
        self::ROLE_AUDITOR => 'Auditor',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRoleNameAttribute(): string
    {
        return self::$roles[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPharmacist(): bool
    {
        return $this->role === self::ROLE_PHARMACIST;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function isInventoryOfficer(): bool
    {
        return $this->role === self::ROLE_INVENTORY_OFFICER;
    }

    public function isAuditor(): bool
    {
        return $this->role === self::ROLE_AUDITOR;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageInventory(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_PHARMACIST, self::ROLE_INVENTORY_OFFICER]);
    }

    public function canAdjustStock(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_INVENTORY_OFFICER]);
    }

    public function canProcessPurchases(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_INVENTORY_OFFICER]);
    }

    public function canProcessSales(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_PHARMACIST, self::ROLE_CASHIER, self::ROLE_MANAGER]);
    }

    public function canProcessReturns(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_PHARMACIST]);
    }

    public function canViewReports(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_AUDITOR]);
    }

    public function canViewAuditLogs(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AUDITOR]);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'sold_by');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'adjusted_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
