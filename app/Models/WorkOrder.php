<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'nojar',
        'title',
        'category',
        'customer_name',
        'customer_phone',
        'location_address',
        'latitude',
        'longitude',
        'coordinator_id',
        'technician_id',
        'device_id',
        'status',
        'status_notes',
        'pickup_time',
        'arrival_time',
        'completion_time',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'arrival_time' => 'datetime',
        'completion_time' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function evidence()
    {
        return $this->hasMany(WorkOrderEvidence::class)->latest();
    }

    public function histories()
    {
        return $this->hasMany(DeviceHistory::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum_dikerjakan' => 'Belum Dikerjakan',
            'perangkat_diambil' => 'Perangkat Diambil di PT',
            'dalam_perjalanan' => 'Dalam Perjalanan',
            'sampai_lokasi' => 'Sampai Lokasi',
            'sedang_dikerjakan' => 'Sedang Dikerjakan',
            'terinstal' => 'Terinstal',
            'pending' => 'Pending / Tertunda',
            'gagal_install' => 'Gagal Install',
            'selesai' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'belum_dikerjakan' => 'bg-slate-100 text-slate-800 border-slate-200',
            'perangkat_diambil' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'dalam_perjalanan' => 'bg-blue-100 text-blue-800 border-blue-200',
            'sampai_lokasi' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'sedang_dikerjakan' => 'bg-amber-100 text-amber-800 border-amber-200',
            'terinstal', 'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'gagal_install' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'Instalasi' => 'bg-teal-50 text-teal-700 border-teal-200',
            'Survei' => 'bg-sky-50 text-sky-700 border-sky-200',
            'Aktivasi' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Troubleshooting' => 'bg-orange-50 text-orange-700 border-orange-200',
            'Dismantle' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }
}
