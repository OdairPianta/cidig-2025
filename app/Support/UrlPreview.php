<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Shows where a record will live on the public site, so "slug" fields can be explained by example:
 * the part that changes with what the person types is highlighted.
 */
class UrlPreview
{
    /**
     * @param  string  $prefix  fixed beginning of the path, e.g. "/webseries/"
     * @param  string|null  $slug  the editable last segment; an empty value shows a placeholder
     */
    public static function html(string $prefix, ?string $slug, string $placeholder = 'endereco-da-pagina'): HtmlString
    {
        $host = e(rtrim(url('/'), '/'));
        $path = e($prefix);
        $slug = filled($slug)
            ? '<strong class="text-primary-600 dark:text-primary-400">' . e($slug) . '</strong>'
            : '<em class="text-gray-400">' . e($placeholder) . '</em>';

        return new HtmlString("<span class=\"break-all text-sm\">{$host}{$path}{$slug}</span>");
    }
}
