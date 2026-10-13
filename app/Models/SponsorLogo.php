<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return \App\Support\Uploads::url($this->path);
    }
}
