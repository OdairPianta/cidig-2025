<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A project mapped by the Observatório form. Only approved projects with coordinates reach the public map.
 */
class MapProject extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'actants' => 'array',
        'tics' => 'array',
        'themes' => 'array',
        'has_own_app' => 'boolean',
        'is_approved' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function scopeOnMap(Builder $query): Builder
    {
        return $query->where('is_approved', true)->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * What the public map receives: no phone, e-mail or anything else the form collected privately.
     * Links are kept only when they are http(s) so a spreadsheet cell can never inject a javascript: URL.
     */
    public function toMapArray(): array
    {
        $link = fn (?string $url): ?string => $url && preg_match('#^https?://#i', trim($url)) ? trim($url) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'city' => $this->city,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'proposers' => $this->proposers,
            'nature' => $this->proposer_nature,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'themes' => $this->themes ?? [],
            'tics' => $this->tics ?? [],
            'website' => $link($this->website),
            'instagram' => $link($this->instagram),
            'facebook' => $link($this->facebook),
            'tiktok' => $link($this->tiktok),
            'app' => $this->has_own_app ? $link($this->app_url) : null,
            'researcher' => $this->researcher_name,
        ];
    }
}
