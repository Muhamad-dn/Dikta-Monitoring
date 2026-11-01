<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderEvidence extends Model
{
    use HasFactory;

    protected $table = 'work_order_evidence';

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'photo_type',
        'photo_path',
        'caption',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getPhotoTypeLabelAttribute(): string
    {
        return match ($this->photo_type) {
            'pengambilan_pt' => 'Bukti Pengambilan di PT',
            'tiba_lokasi' => 'Bukti Tiba di Lokasi',
            'hasil_instalasi' => 'Foto Hasil Instalasi Perangkat',
            'redaman_ont' => 'Foto Pengukuran Redaman / OPM',
            'kendala' => 'Foto Kendala / Pending',
            default => ucfirst(str_replace('_', ' ', $this->photo_type)),
        };
    }
}
