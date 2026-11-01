<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'teknisi';
    }

    public function workOrdersAsTechnician()
    {
        return $this->hasMany(WorkOrder::class, 'technician_id');
    }

    public function workOrdersAsCoordinator()
    {
        return $this->hasMany(WorkOrder::class, 'coordinator_id');
    }

    public function carriedDevices()
    {
        return $this->hasMany(Device::class, 'current_technician_id');
    }

    public function devices()
    {
        return $this->carriedDevices();
    }
}
