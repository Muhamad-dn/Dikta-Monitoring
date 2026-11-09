<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'device_id',
        'registration_number',
        'device_name',
        'area',
        'system_status',
        'system_location',
        'physical_status',
        'scanned_at',
        'scanned_by',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->physical_status) {
            'matched' => [
                'label' => 'Sesuai (Match)',
                'bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                'dot' => 'bg-emerald-400',
            ],
            'wrong_location' => [
                'label' => 'Anomali / Posisi Beda',
                'bg' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                'dot' => 'bg-amber-400',
            ],
            'surplus_unregistered' => [
                'label' => 'Fisik Tak Terdaftar',
                'bg' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                'dot' => 'bg-purple-400',
            ],
            default => [
                'label' => 'Belum Di-Scan',
                'bg' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                'dot' => 'bg-rose-400',
            ],
        };
    }
}
