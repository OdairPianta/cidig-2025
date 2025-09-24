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
    'user_id',
    'video_id',
    'videos_json',
    'thumbnail',
    'thumbnail_novo',
    'background_image',
    'background_img',
    'background_credit',
    'is_active',
];

protected $casts = [
    'is_active' => 'boolean',
    'videos_json' => 'array',
];

public function videos()
{
    $videoIds = $this->videos_json ?? ($this->video_id ? [$this->video_id] : []);
    
    if (empty($videoIds)) {
        return collect([]);
    }
    
    return Video::whereIn('id', $videoIds)->get();
}

public function addVideo(int $videoId)
{
    $currentIds = $this->video_id ?? [];
    if (!in_array($videoId, $currentIds)) {
        $currentIds[] = $videoId;
        $this->update(['video_id' => $currentIds]);
    }
}

public function removeVideo(int $videoId)
{
    $currentIds = $this->video_id ?? [];
    $newIds = array_values(array_filter($currentIds, fn($id) => $id !== $videoId));
    $this->update(['video_id' => $newIds]);
}

    public function user()
    {
        return $this->belongsTo(User::class);
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