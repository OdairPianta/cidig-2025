<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SponsorLogo extends Model
{
    protected $fillable = ['sponsor_id', 'path', 'label', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->exists($this->path)
            ? Storage::disk('public')->url($this->path)
            : url($this->path);
    }
}
