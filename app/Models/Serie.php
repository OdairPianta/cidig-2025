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
        'background_image',
        'background_credit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'serie_user', 'serie_id', 'user_id');
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'series_videos', 'serie_id', 'video_id')
            ->withPivot(['title', 'description', 'position'])
            ->withTimestamps()
            ->orderBy('series_videos.position')
            ->orderBy('series_videos.created_at');
    }

    public function addVideo(int $videoId)
    {
        if (!$this->videos()->where('video_id', $videoId)->exists()) {
            $this->videos()->attach($videoId);
        }
    }

    public function removeVideo(int $videoId)
    {
        $this->videos()->detach($videoId);
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'serie_sponsor', 'serie_id', 'sponsor_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'serie_tag', 'serie_id', 'tag_id');
    }
}