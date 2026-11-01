<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'spec_code',
        'name',
        'brand',
        'type',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class, 'kode_spek', 'spec_code');
    }
}
