<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // master, manager, karyawan
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Cek apakah user punya role tertentu - Ben hak aksese bener.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah user itu Master (Sing duwe toko).
     */
    public function isMaster(): bool
    {
        return $this->role === 'master';
    }

    /**
     * Cek apakah user itu Manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Cek apakah user itu Karyawan.
     */
    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    /**
     * Transactions created by this user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    /**
     * Activity logs by this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
