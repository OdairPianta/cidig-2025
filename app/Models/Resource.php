<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use SoftDeletes;

    const FORMAT_TEXT = 1;
    const FORMAT_VIDEO = 2;
    const FORMAT_AUDIO = 3;
    const FORMAT_IMAGE = 4;

    const FORMAT_ICONS = [
        self::FORMAT_TEXT => 'far fa-file-alt',
        self::FORMAT_VIDEO => 'fas fa-video',
        self::FORMAT_AUDIO => 'fas fa-volume-up',
        self::FORMAT_IMAGE => 'fas fa-image',
    ];

    const FORMAT_TYPES = [
        self::FORMAT_TEXT => 'Texto',
        self::FORMAT_VIDEO => 'Vídeo',
        self::FORMAT_AUDIO => 'Audio',
        self::FORMAT_IMAGE => 'Imagem',
    ];

    protected $fillable = [
        'title', 'author', 'key_words', 'description', 'publisher',
        'source', 'format_id', 'language', 'subject_id', 'created_by',
        'deleted_by', 'published_at', 'coverage', 'contributor',
        'copy_rights', 'original_date', 'format', 'identifier',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All metadata as plain text, ready to paste elsewhere ("easy copy").
     */
    public function toCopyText(): string
    {
        $lines = [
            'Título' => $this->title,
            'Verbete' => $this->subject?->name,
            'Tipo de conteúdo' => self::FORMAT_TYPES[$this->format_id] ?? null,
            'Tags' => $this->tags->pluck('name')->join(', '),
            'Descrição' => $this->description,
            'Fonte (link)' => $this->source ? $this->getSourceLink() : null,
            'Cobertura' => $this->coverage,
            'Autor' => $this->author,
            'Editora ou canal de publicação' => $this->publisher,
            'Co-autor ou contribuidor' => $this->contributor,
            'Direitos' => $this->copy_rights,
            'Data' => $this->original_date,
            'Formato' => $this->format,
            'Identificador' => $this->identifier,
            'Idioma' => $this->language,
        ];

        return collect($lines)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $label) => "{$label}: {$value}")
            ->implode("\n");
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    /**
     * Syncs the authors typed in the form (";" also separates names) and refreshes the plain-text column.
     */
    public function syncAuthorNames(array $names): void
    {
        $ids = collect($names)
            ->flatMap(fn ($name) => Author::parseNames((string) $name))
            ->map(fn (string $name) => Author::findOrCreateByName($name)->getKey())
            ->unique()
            ->all();

        $this->authors()->sync($ids);
        $this->forceFill(['author' => self::authorText($ids)])->saveQuietly();
    }

    /**
     * Plain-text author list, kept on the row because the public pages and search read it.
     */
    public static function authorText(array $authorIds): string
    {
        return Author::whereIn('id', $authorIds)->orderBy('name')->pluck('name')->join('; ');
    }

    /**
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasOne
     */
    public function uploadedFile()
    {
        return $this->hasOne(UploadedFile::class);
    }

    /**
     * @return string
     */
    public function getFormatDescription(): string
    {
        return self::FORMAT_TYPES[$this->format_id];
    }

    /**
     * @param bool $newVersion
     * @return string
     */
    public function getFormatIcon(bool $newVersion = false): string
    {
        if ($newVersion && $this->format_id === self::FORMAT_TEXT) {
            return 'fa fa-link';
        }

        return self::FORMAT_ICONS[$this->format_id];
    }

    public function getNewFormatIcon(): string
    {
        return match ($this->format_id) {
            self::FORMAT_VIDEO => 'heroicon-o-video-camera',
            self::FORMAT_AUDIO => 'heroicon-o-speaker-wave',
            self::FORMAT_IMAGE => 'heroicon-o-photo',
            default => 'heroicon-o-document',
        };
    }

    /**
     * @param int $tagId
     * @return bool
     */
    public function isTagSelected(int $tagId): bool
    {
        foreach ($this->tags as $tag){
            if($tag->id === $tagId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getSourceLink(): string
    {
        if( $this->uploadedFile()->count() ) {
            return \App\Support\Uploads::url($this->source);
        }

        return $this->source;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * @return bool
     */
    public function hasUploadedFile(): bool
    {
        return $this->uploadedFile()->count();
    }

    /**
     * @return string
     */
    public function getUploadedFileOriginalName(): string
    {
        if ($this->uploadedFile){
            return $this->uploadedFile->original_name;
        }

        return $this->source;
    }

    public function scopeGetByTagIds(Builder $query, array $tagIds)
    {
        return $query->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        });
    }
}
