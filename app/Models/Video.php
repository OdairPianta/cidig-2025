<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Video extends Model
{
    protected $table = 'videos';

    protected $fillable = [
        'subject_id',
        'video_id',
        'title',
        'description',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured'   => 'boolean',
        'is_active'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accepts a full YouTube URL (watch, youtu.be, embed, shorts) or a bare ID
     * and returns only the 11-character video ID.
     */
    public static function extractYoutubeId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~', $value, $matches)) {
            return $matches[1];
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $value) ? $value : null;
    }

    public function setVideoIdAttribute($value): void
    {
        $this->attributes['video_id'] = self::extractYoutubeId($value) ?? trim((string) $value);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function series()
    {
        return $this->belongsToMany(Serie::class, 'series_videos', 'video_id', 'serie_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'video_user');
    }
}