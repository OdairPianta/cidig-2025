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
     * Markdown (inline HTML is allowed, older posts were saved as HTML) turned into safe HTML:
     * scripts and unsafe attributes are removed, links open in a new tab and only YouTube/Vimeo
     * embeds are kept as iframes.
     */
    public static function renderContent(?string $content): string
    {
        // GitHub-flavoured markdown minus its "disallowed raw HTML" filter, which would escape <iframe>;
        // the purifier below is what makes the HTML safe.
        $environment = new \League\CommonMark\Environment\Environment(['html_input' => 'allow', 'allow_unsafe_links' => false]);
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Table\TableExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Strikethrough\StrikethroughExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Autolink\AutolinkExtension());

        $html = (string) (new \League\CommonMark\MarkdownConverter($environment))->convert((string) $content);

        return Purifier::clean($html, [
            'HTML.Allowed' => 'p,br,strong,b,em,i,u,s,h1,h2,h3,h4,ul,ol,li,blockquote,pre,code,hr,a[href|title|target|rel],img[src|alt|width|height],figure,figcaption,table,thead,tbody,tr,th,td,span,div,iframe[src|width|height|title|allowfullscreen|frameborder]',
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'HTML.TargetBlank' => true,
            'HTML.Nofollow' => false,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true],
            'AutoFormat.RemoveEmpty' => false,
        ]);
    }

    public function getSafeContentAttribute(): string
    {
        return self::renderContent($this->content);
    }

    /**
     * Plain-text summary for listing cards (decodes entities such as &nbsp;).
     */
    public function getExcerptAttribute(): string
    {
        $text = html_entity_decode(strip_tags(self::renderContent($this->content)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);

        return Str::limit(trim($text), 150);
    }

    /** Names shown as the byline: the credited authors, or the account that posted. */
    public function getBylineAttribute(): string
    {
        return $this->authors->isNotEmpty()
            ? $this->authors->pluck('name')->join('; ')
            : (string) $this->user?->name;
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_post')->orderBy('author_post.position');
    }

    /** Syncs credited authors from names typed in the form (";" also separates names). */
    public function syncAuthorNames(array $names): void
    {
        $ids = [];
        foreach (collect($names)->flatMap(fn ($n) => Author::parseNames((string) $n))->unique() as $position => $name) {
            $ids[Author::findOrCreateByName($name)->getKey()] = ['position' => $position];
        }

        $this->authors()->sync($ids);
    }

    public function getImageLinkAttribute(): ?string
    {
        return $this->image_url ? \App\Support\Uploads::url($this->image_url) : null;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNull('archived_at')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
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
            $base = Str::slug($post->slug ?: $post->title) ?: 'post';
            $slug = $base;

            for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
                $slug = "{$base}-{$i}";
            }

            $post->slug = $slug;
        });
    }
}
