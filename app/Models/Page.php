<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A page of the public site that the admin builds without code. The template decides which
 * fields make sense and how the page is laid out.
 */
class Page extends Model
{
    public const TEMPLATE_TEXT = 'text';
    public const TEMPLATE_PUBLICATIONS = 'publications';
    public const TEMPLATE_CASES = 'cases';
    public const TEMPLATE_OBSERVATORY = 'observatory';

    public const TEMPLATES = [
        self::TEMPLATE_TEXT => 'Texto livre',
        self::TEMPLATE_PUBLICATIONS => 'Publicações (livros e artigos)',
        self::TEMPLATE_CASES => 'Estudos de caso',
        self::TEMPLATE_OBSERVATORY => 'Observatório',
    ];

    /** Label of the "category" field for each template. */
    public const CATEGORY_LABELS = [
        self::TEMPLATE_PUBLICATIONS => 'Tipo (Livro, Artigo…)',
        self::TEMPLATE_CASES => 'País ou região',
        self::TEMPLATE_OBSERVATORY => 'País ou região',
    ];

    protected $fillable = ['title', 'slug', 'template', 'summary', 'body', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(PageItem::class)->orderBy('position')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function hasItems(): bool
    {
        return $this->template !== self::TEMPLATE_TEXT;
    }

    public function getUrlAttribute(): string
    {
        return route('v2.page', $this->slug);
    }

    public function getSafeBodyAttribute(): string
    {
        return Post::renderContent($this->body);
    }
}
