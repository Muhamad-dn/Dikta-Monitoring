<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'technician_id',
        'actor_id',
        'type',
        'title',
        'message',
        'registration_number',
        'device_name',
        'hub',
        'source',
        'target_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getUrlAttribute(): string
    {
        if (!empty($this->target_url)) {
            return $this->target_url;
        }

        $hub = in_array($this->hub, ['hb_ckr', 'tersedia_hb_ckr']) ? 'hb_ckr' : 'hb_jkt';

        return route('admin.devices.stock', [
            'hub' => $hub,
            'search' => $this->registration_number,
            'highlight' => $this->device_id,
        ]);
    }

    public function getHubLabelAttribute(): string
    {
        return in_array($this->hub, ['hb_ckr', 'tersedia_hb_ckr']) ? 'HB CKR' : 'HB JKT';
    }

    public function getHubBadgeClassAttribute(): string
    {
        return in_array($this->hub, ['hb_ckr', 'tersedia_hb_ckr'])
            ? 'bg-teal-100 text-teal-800 dark:bg-teal-950/70 dark:text-teal-300 border border-teal-300 dark:border-teal-700'
            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700';
    }
}
