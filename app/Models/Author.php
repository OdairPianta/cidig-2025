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
