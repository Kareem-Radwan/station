<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isAccountant(): bool   { return $this->role === 'accountant'; }
    public function isEngineer(): bool     { return $this->role === 'engineer'; }
    public function isInventoryOfficer(): bool { return $this->role === 'inventory_officer'; }
    public function isInventoryManager(): bool { return $this->role === 'inventory_manager'; }
    public function isRental(): bool           { return $this->role === 'rental'; }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function auditLogs() { return $this->hasMany(AuditLog::class); }

    public static function roleLabel(string $role): string
    {
        return match($role) {
            'admin'              => 'مدير النظام',
            'accountant'         => 'محاسب',
            'engineer'           => 'مهندس',
            'inventory_officer'  => 'مسؤول المخزن',
            'inventory_manager'  => 'مدير المخزون',
            'rental'             => 'مسؤول المعدات والتأجير',
            default              => $role,
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleLabel($this->role);
    }
}
