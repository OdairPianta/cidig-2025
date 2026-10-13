<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class University extends Model
{
    protected $fillable = ['name', 'aliases', 'normalized_name', 'is_active'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'aliases' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (University $university) {
            $university->name = trim(preg_replace('/\s+/', ' ', $university->name));
            $university->normalized_name = self::normalize($university->name);
        });
    }

    /**
     * Lowercase, accent-free, punctuation-free form used to detect duplicates
     * ("Universidade de São Paulo" == "universidade de sao paulo").
     */
    public static function normalize(string $name): string
    {
        $name = Str::lower(Str::ascii($name));
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);

        return trim($name);
    }

    /**
     * Finds an institution whose name or alias matches (ignoring case, accents and punctuation).
     */
    public static function findByName(string $name): ?self
    {
        $normalized = self::normalize($name);

        if ($normalized === '') {
            return null;
        }

        $match = self::where('normalized_name', $normalized)->first();

        if ($match) {
            return $match;
        }

        return self::whereNotNull('aliases')->get()->first(
            fn (self $university) => collect($university->aliases)
                ->contains(fn ($alias) => self::normalize((string) $alias) === $normalized)
        );
    }

    /**
     * Reuses an existing institution when the name matches, otherwise creates it.
     */
    public static function findOrCreateByName(string $name): self
    {
        return self::findByName($name) ?? self::create(['name' => $name, 'is_active' => true]);
    }

    /**
     * "Did you mean…" suggestions: similar names or aliases (typos, other languages).
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function similarTo(string $name, ?int $ignoreId = null, int $limit = 3)
    {
        $needle = self::normalize($name);

        if ($needle === '') {
            return collect();
        }

        return self::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->get()
            ->map(function (self $university) use ($needle) {
                $candidates = array_merge([$university->name], $university->aliases ?? []);
                $best = 0;

                foreach ($candidates as $candidate) {
                    $candidate = self::normalize((string) $candidate);
                    similar_text($needle, $candidate, $percent);

                    if ($candidate !== '' && (str_contains($candidate, $needle) || str_contains($needle, $candidate))) {
                        $percent = max($percent, 85);
                    }

                    $best = max($best, $percent);
                }

                return ['university' => $university, 'score' => $best];
            })
            ->filter(fn ($row) => $row['score'] >= 70)
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('university')
            ->values();
    }

    public function userInformations()
    {
        return $this->hasMany(UserInformation::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', '=', true);
    }

    public function scopeByName(Builder $query, string $name)
    {
        return $query->where('name', '=', $name);
    }

    public function scopeByIds(Builder $query, array $ids)
    {
        return $query->whereIn('id', $ids);
    }
}
