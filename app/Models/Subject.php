<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Subject extends Model
{
    use SoftDeletes;

    const ALGORITMOS_CIDADANIA = 'algoritmos-cidadania';
    const BLOCKCHAIN_CIDADANIA = 'blockchain-cidadania';
    const BLOCKCHAIN_CIDADANIA_SERIE = 'serie-blockchain-cidadania';
    const CIDADANIA_TERCEIRO_MILENIO = 'cidadania-terceiro-milenio';
    const CODICE = 'codice';
    const COMPETENCIAS_CIDADANIA_SECULO_XXI = 'competencias-cidadania-seculo-21';
    const DATA_ECOLOGIA_CLIMA = 'data-ecologia-clima';
    const DESIGN_PLATAFORMAS_DIGITAIS = 'design-plataformas-digitais';
    const DIALOGOS_ATOPICOS = 'dialogos-atopicos';
    const DIGITALIZACAO_MEMORIA = 'digitalizacao-memoria';
    const DIREITO_SAUDE_DIGITAL = 'direito-saude-digital';
    const ECOLOGIA_INFORMACAO = 'ecologia-informacao';
    const GAMES_CIDADANIA = 'games-cidadania';
    const GOVERNANCA_DIGITAL = 'governanca-digital';
    const GREEN_DATA = 'green-data';
    const JORNALISMOS_POSSIVEIS = 'jornalismos-possiveis';
    const MEDICINA_DADOS = 'medicina-dados';
    const NET_ATIVISMO_CULTURA_HACKER = 'net-ativismo-cultura-hacker';
    const NET_ATIVISMO_INDIGENA = 'net-ativismo-indigena';
    const NOVAS_FORMAS_CIDADANIA = 'novas-formas-cidadania';
    const ONTOLOGIAS_CONTEMPORANEAS = 'ontologias-contemporaneas';
    const POS_CIDADES_REDES_DE_CIDADANIA = 'pos-cidades';
    const TEORIAS_NOVAS_FORMAS_CIDADANIA = 'teorias-novas-formas-cidadania';
    const TRANSLITERACIA_CIDADANIA_DIGITAL = 'transliteracia-cidadania-digital';
    const UNICO = 'unico';

    const DEFAULT_BACKGROUND_IMAGE = '/images/team/background.jpg';



    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'background-image',
        'background-credit',
        'constant_key',
        'video_id',
        'series_mapping',
        'content_data',
        'is_series',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'is_series' => 'boolean',
        'series_mapping' => 'array',
        'content_data' => 'array',
    ];

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSeries(Builder $query): Builder
    {
        return $query->where('is_series', true);
    }

    public function scopeSubjects(Builder $query): Builder
    {
        return $query->where('is_series', false);
    }

    public static function findByConstant(string $constantKey): ?self
    {
        return static::where('constant_key', $constantKey)->first();
    }

    public static function getSeriesSubjectMap(): array
    {
        return static::series()
            ->whereNotNull('series_mapping')
            ->get()
            ->pluck('series_mapping', 'constant_key')
            ->toArray();
    }

    public static function getSubjectNamesImages(): array
    {
        return static::subjects()
            ->get()
            ->mapWithKeys(function ($subject) {
                return [
                    $subject->constant_key => [
                        'name' => $subject->name,
                        'image' => $subject->image,
                        'id' => $subject->constant_key,
                        'inactive' => !$subject->is_active,
                    ]
                ];
            })
            ->toArray();
    }

    public static function getSubjectPagesContent(): array
    {
        return static::subjects()
            ->whereNotNull('content_data')
            ->get()
            ->mapWithKeys(function ($subject) {
                return [$subject->constant_key => $subject->content_data];
            })
            ->toArray();
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function canBeExcluded(): bool
    {
        return !($this->resources()->exists() || $this->users()->exists());
    }

    public function isTagAssociated(int $tagId): bool
    {
        return $this->tags->contains('id', $tagId);
    }

    public static function getSeriesIdsFromSubjectId(string $subject): array
    {
        $seriesMap = static::getSeriesSubjectMap();
        $seriesIds = [];
        
        foreach ($seriesMap as $seriesName => $subjects) {
            if (in_array($subject, $subjects)) {
                $seriesIds[] = $seriesName;
            }
        }

        return $seriesIds;
    }
}