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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'video_user');
    }
}