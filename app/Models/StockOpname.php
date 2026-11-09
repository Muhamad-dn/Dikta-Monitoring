<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_code',
        'title',
        'period_month',
        'period_year',
        'location',
        'status',
        'total_system',
        'total_physical',
        'total_matched',
        'total_missing',
        'total_surplus',
        'notes',
        'conducted_by',
        'admin_name',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'period_month' => 'integer',
        'period_year' => 'integer',
        'total_system' => 'integer',
        'total_physical' => 'integer',
        'total_matched' => 'integer',
        'total_missing' => 'integer',
        'total_surplus' => 'integer',
    ];

    public const LOCATIONS = [
        'semua' => 'Semua Lokasi / Gudang',
        'hb_jkt' => 'Gudang HB JKT',
        'hb_ckr' => 'Gudang HB CKR',
    ];

    public const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function conductor()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function getMonthNameAttribute(): string
    {
        return self::MONTHS[$this->period_month] ?? 'Bulan ' . $this->period_month;
    }

    public function getLocationLabelAttribute(): string
    {
        return self::LOCATIONS[$this->location] ?? ucfirst(str_replace('_', ' ', $this->location));
    }

    public function recalculateTotals(): void
    {
        $this->total_system = $this->items()->whereNotIn('physical_status', ['surplus_unregistered'])->count();
        $this->total_matched = $this->items()->where('physical_status', 'matched')->count();
        $this->total_missing = $this->items()->where('physical_status', 'missing')->count();
        $this->total_surplus = $this->items()->whereIn('physical_status', ['surplus_unregistered', 'wrong_location'])->count();
        $this->total_physical = $this->total_matched + $this->total_surplus;
        $this->save();
    }
}

