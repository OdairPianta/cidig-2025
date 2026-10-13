<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title', 
        'slug',
        'content',
        'image_url',
        'is_published',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Post body cleaned of scripts/unsafe attributes; links open in a new tab.
     */
    public function getSafeContentAttribute(): string
    {
        return Purifier::clean((string) $this->content, [
            'HTML.Allowed' => 'p,br,strong,b,em,i,u,s,h1,h2,h3,h4,ul,ol,li,blockquote,pre,code,hr,a[href|title|target|rel],img[src|alt|width|height],figure,figcaption,table,thead,tbody,tr,th,td,span,div',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'HTML.TargetBlank' => true,
            'HTML.Nofollow' => false,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],
            'AutoFormat.RemoveEmpty' => false,
        ]);
    }

    /**
     * Plain-text summary for listing cards (decodes entities such as &nbsp;).
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags((string) $this->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);

        return Str::limit(trim($text), 150);
    }

    public function getImageLinkAttribute(): ?string
    {
        return $this->image_url ? Storage::disk('public')->url($this->image_url) : null;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Public URL of the post on the website (author slug + post slug).
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (! $this->user || ! $this->slug) {
            return null;
        }

        return route('post.show', [Str::slug($this->user->name), $this->slug]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }
}
