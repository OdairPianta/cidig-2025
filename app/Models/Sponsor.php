<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $table = 'sponsors';

    public $fillable = [
        'id',
        'name',
        'contact_name',
        'email',
        'landline',
        'mobile',
        'address',
        'image',
    ];

    public function logos()
    {
        return $this->hasMany(SponsorLogo::class);
    }

    /**
     * Keeps exactly one logo "em uso" and mirrors it into `image`, which the site reads.
     * If several are marked, the first one wins; if none is, the first logo is used.
     */
    public function syncActiveLogo(): void
    {
        $logos = $this->logos()->orderBy('id')->get();
        $active = $logos->firstWhere('is_active', true) ?? $logos->first();

        foreach ($logos as $logo) {
            $shouldBeActive = $active !== null && $logo->is($active);

            if ($logo->is_active !== $shouldBeActive) {
                $logo->update(['is_active' => $shouldBeActive]);
            }
        }

        $this->forceFill(['image' => $active?->path])->saveQuietly();
    }

    public function series()
    {
        return $this->belongsToMany(Serie::class, 'serie_sponsor', 'sponsor_id', 'serie_id');
    }
}
