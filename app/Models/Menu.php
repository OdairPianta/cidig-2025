<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = [
        'link',
        'page_id',
        'slug',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /** Items pointing at a page follow the page's address, so renaming its slug never breaks the menu. */
    public function getLinkAttribute($value)
    {
        return $this->page_id && $this->page ? $this->page->url : $value;
    }

    // Scopes
    public function scopeActive($query)
    {
        // An item pointing at an unpublished page stays out of the public menu.
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('page_id')->orWhereHas('page', fn ($page) => $page->where('is_published', true)));
    }

    public function scopeOrdered($query)
    {
    return $query->orderBy('order')->orderBy('slug');
    }
}