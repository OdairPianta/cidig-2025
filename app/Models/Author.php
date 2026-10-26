<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Author extends Model
{
    protected $fillable = ['name', 'normalized_name'];

    protected static function booted(): void
    {
        static::saving(function (Author $author) {
            $author->name = trim(preg_replace('/\s+/', ' ', $author->name));
            $author->normalized_name = self::normalize($author->name);
        });
    }

    /**
     * Key used to detect duplicates: lowercase, no accents or punctuation,
     * so "José  da Silva" and "jose da silva." are the same person.
     */
    public static function normalize(string $name): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($name))));
    }

    /**
     * Splits a free-text author field into individual names.
     *
     * Separators are ";", newlines, "&", " e " and " and ". A comma separates people
     * ("Ana Souza, Bruno Lima") unless the first part looks like a surname
     * ("Haraway, Donna", "DI FELICE, Massimo"), in which case it is turned into "Donna Haraway".
     * Role notes in parentheses are dropped.
     *
     * @return list<string>
     */
    public static function parseNames(string $text): array
    {
        $text = preg_replace('/\([^)]*\)/u', '', $text);
        $segments = preg_split('/\s*(?:;|\n|&)\s*|\s+(?:e|and)\s+/u', $text);
        $names = [];

        foreach ($segments as $segment) {
            $parts = array_values(array_filter(array_map(
                fn ($part) => trim(preg_replace('/(?<=\p{L}{3})\.$/u', '', trim($part, " \t,")), " \t"),
                explode(',', $segment)
            ), fn ($part) => $part !== ''));

            if (count($parts) === 2 && self::looksLikeSurname($parts[0])) {
                $names[] = self::titleCaseIfShouting($parts[1]) . ' ' . self::titleCaseIfShouting($parts[0]);
            } else {
                array_push($names, ...$parts);
            }
        }

        return $names;
    }

    private static function looksLikeSurname(string $part): bool
    {
        $particles = ['di', 'de', 'da', 'do', 'dos', 'das', 'del', 'van', 'von', 'der', 'le', 'la', 'el', 'al'];

        return ! str_contains($part, ' ')
            || self::isShouting($part)
            || in_array(mb_strtolower(strtok($part, ' ')), $particles, true);
    }

    private static function isShouting(string $value): bool
    {
        return preg_match('/\p{L}/u', $value) && $value === mb_strtoupper($value);
    }

    private static function titleCaseIfShouting(string $value): string
    {
        return self::isShouting($value) ? mb_convert_case($value, MB_CASE_TITLE) : $value;
    }

    public static function findOrCreateByName(string $name): self
    {
        $normalized = self::normalize($name);

        return self::where('normalized_name', $normalized)->first()
            ?? self::create(['name' => $name]);
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class);
    }
}
