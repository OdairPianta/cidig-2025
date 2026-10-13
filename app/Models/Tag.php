<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'normalized_name', 'is_active'];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            $tag->name = trim(preg_replace('/\s+/', ' ', $tag->name));
            $tag->normalized_name = self::normalize($tag->name);
        });
    }

    /**
     * Canonical form used to detect duplicates: lowercase, no accents or punctuation,
     * and each word without its (Portuguese/English) plural ending, so that
     * "Algoritmos", "algoritmo" and "Algorítmo" collapse to the same key.
     */
    public static function normalize(string $name): string
    {
        $name = preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($name)));

        return collect(explode(' ', trim($name)))
            ->filter()
            ->map(function (string $word) {
                if (strlen($word) > 4 && str_ends_with($word, 'oes')) {
                    return substr($word, 0, -3) . 'ao';
                }
                if (strlen($word) > 4 && str_ends_with($word, 'ns')) {
                    return substr($word, 0, -2) . 'm';
                }
                if (strlen($word) > 3 && str_ends_with($word, 's') && ! str_ends_with($word, 'ss')) {
                    return substr($word, 0, -1);
                }

                return $word;
            })
            ->implode(' ');
    }

    /**
     * Existing tag with the same canonical name (ignoring case, accents and plurals).
     */
    public static function findDuplicateOf(string $name, ?int $ignoreId = null): ?self
    {
        $normalized = self::normalize($name);

        if ($normalized === '') {
            return null;
        }

        return self::where('normalized_name', $normalized)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->first();
    }

    /**
     * Groups of tags that share the same canonical name.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, self>>
     */
    public static function duplicateGroups()
    {
        return self::query()
            ->whereNotNull('normalized_name')
            ->get()
            ->groupBy('normalized_name')
            ->filter(fn ($group) => $group->count() > 1);
    }

    /**
     * Merges the given tags into this one, re-pointing every pivot row, then deletes them.
     */
    public function mergeFrom(iterable $others): int
    {
        $merged = 0;

        DB::transaction(function () use ($others, &$merged) {
            foreach ($others as $other) {
                if ($other->getKey() === $this->getKey()) {
                    continue;
                }

                foreach ([['subject_tag', 'subject_id'], ['resource_tag', 'resource_id'], ['serie_tag', 'serie_id']] as [$table, $column]) {
                    $already = DB::table($table)->where('tag_id', $this->getKey())->pluck($column);

                    DB::table($table)->where('tag_id', $other->getKey())->whereIn($column, $already)->delete();
                    DB::table($table)->where('tag_id', $other->getKey())->update(['tag_id' => $this->getKey()]);
                }

                $other->delete();
                $merged++;
            }
        });

        return $merged;
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * @return BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * @return BelongsToMany
     */
    public function resources()
    {
        return $this->belongsToMany(Resource::class);
    }

    public function series()
    {
        return $this->belongsToMany(Serie::class, 'serie_tag', 'tag_id', 'serie_id');
    }

    /**
     * @return bool
     */
    public function canBeExcluded()
    {
        return ! ($this->subjects()->exists() || $this->resources()->exists());
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', '=', true);
    }

    /**
     * @param array $storeData
     * @return \App\Models\Tag|null
     */
    public static function findOrCreate(array $storeData):? Tag
    {
        $name = trim((string) ($storeData['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        return self::findDuplicateOf($name) ?? self::create(array_merge($storeData, ['name' => $name]));
    }

    public function scopeByName(Builder $query, string $name)
    {
        return $query->where('name', '=', $name);
    }

    public function scopeByIds(Builder $query, array $ids)
    {
        return $query->whereIn('id', $ids);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', '=', 1);
    }
}
