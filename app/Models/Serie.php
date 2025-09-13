<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $table = 'series';

    protected $fillable = [
        'id',
        'slug',
        'title',
        'description',
        'thumbnail',
        'thumbnail_novo',
        'background_image',
        'background_img',
        'background_credit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'serie_sponsor', 'serie_id', 'sponsor_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'serie_tag', 'serie_id', 'tag_id');
    }
}
