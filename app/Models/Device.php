<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'kode_spek',
        'serial_number',
        'name',
        'brand',
        'model',
        'type',
        'nojar',
        'customer_name',
        'address',
        'area',
        'activity',
        'stock_status',
        'current_technician_id',
        'current_location',
        'condition',
        'notes',
        'returned_to_lintasarta',
    ];

    public const AREAS = [
        'BWA',
        'BDA',
        'SEOA',
        'SAST',
        'CDA',
        'NEA',
    ];

    public const LINTASARTA_DESTINATIONS = [
        'Gudang Tekno',
        'BWA',
        'BDA',
        'SEOA',
        'SAST',
        'CDA',
        'NEA',
    ];

    public function specification()
    {
        return $this->belongsTo(DeviceSpecification::class, 'kode_spek', 'spec_code');
    }

    public function currentTechnician()
    {
        return $this->belongsTo(User::class, 'current_technician_id');
    }

    public function histories()
    {
        return $this->hasMany(DeviceHistory::class)->latest();
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class)->latest();
    }

    public function evidence()
    {
        return $this->hasManyThrough(WorkOrderEvidence::class, WorkOrder::class, 'device_id', 'work_order_id');
    }

    public function activeWorkOrder()
    {
        return $this->hasOne(WorkOrder::class)
            ->whereNotIn('status', ['selesai', 'terinstal'])
            ->latestOfMany();
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta' => 'Tersedia di HB JKT',
            'tersedia_hb_ckr' => 'Tersedia di HB CKR',
            'dibawa_teknisi' => 'Dibawa Teknisi',
            'terpasang_pelanggan', 'terinstal' => 'Terinstal',
            'pending' => 'Pending',
            'rusak' => 'Rusak',
            'hilang' => 'Hilang',
            'dismantle' => 'Dismantle',
            default => ucfirst(str_replace('_', ' ', $this->stock_status)),
        };
    }

    public function getFieldStatusLabelAttribute(): string
    {
        if ($this->activeWorkOrder) {
            $woStatus = strtolower($this->activeWorkOrder->status);
            if ($woStatus === 'sampai_lokasi') return 'SAMPAI LOKASI';
            if ($woStatus === 'sedang_dikerjakan') return 'SEDANG DIKERJAKAN';
            if ($woStatus === 'dalam_perjalanan') return 'DALAM PERJALANAN';
            if ($woStatus === 'pending') return 'PENDING';
            if (in_array($woStatus, ['terinstal', 'selesai'])) return 'TERINSTAL';
        }

        $latestHistory = $this->histories->first();
        if ($latestHistory) {
            $action = strtoupper($latestHistory->action ?? '');
            $notes = strtoupper($latestHistory->notes ?? '');
            
            if (str_contains($action, 'SAMPAI') || str_contains($notes, 'STATUS: SAMPAI LOKASI')) {
                return 'SAMPAI LOKASI';
            }
            if (str_contains($action, 'PENDING') || str_contains($notes, 'STATUS: PENDING')) {
                return 'PENDING';
            }
            if (str_contains($action, 'GAGAL') || str_contains($notes, 'STATUS: GAGAL INSTAL')) {
                return 'GAGAL INSTAL';
            }
            if (str_contains($action, 'TERINSTAL') || str_contains($notes, 'STATUS: TERINSTAL')) {
                return 'TERINSTAL';
            }
            if (str_contains($action, 'RUSAK') || str_contains($notes, 'STATUS: RUSAK')) {
                return 'RUSAK';
            }
            if (str_contains($action, 'HILANG') || str_contains($notes, 'STATUS: HILANG')) {
                return 'HILANG';
            }
            if (str_contains($action, 'KEMBALI') || str_contains($notes, 'KEMBALI')) {
                return 'DIBAWA KEMBALI';
            }
        }

        if ($this->stock_status === 'pending') return 'PENDING';
        if ($this->stock_status === 'rusak') return 'RUSAK';
        if ($this->stock_status === 'hilang') return 'HILANG';

        return 'DIBAWA TEKNISI';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'tersedia_hb_ckr' => 'bg-teal-100 text-teal-800 border-teal-200',
            'dibawa_teknisi' => 'bg-amber-100 text-amber-800 border-amber-200',
            'terpasang_pelanggan', 'terinstal' => 'bg-blue-100 text-blue-800 border-blue-200',
            'pending' => 'bg-orange-100 text-orange-800 border-orange-200',
            'rusak' => 'bg-rose-100 text-rose-800 border-rose-200',
            'hilang' => 'bg-purple-100 text-purple-800 border-purple-200',
            'dismantle' => 'bg-slate-100 text-slate-800 border-slate-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function isAvailableInWarehouse(): bool
    {
        return in_array($this->stock_status, ['tersedia_hb_jkt', 'tersedia_hb_ckr', 'tersedia_pt', 'tersedia_hb_dikta']);
    }

    public function getLinearBarcodeSvgAttribute(): string
    {
        return \App\Services\BarcodeService::code128Svg($this->registration_number ?? 'REG');
    }
}
