<?php

namespace App\Filament\Pages;

use App\Models\Tag;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TagAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Conteúdos';

    protected static ?string $navigationLabel = 'Análise de Tags';

    protected static ?string $title = 'Análise de Tags';

    protected static string $view = 'filament.pages.tag-analytics';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Usage per tag across verbetes, conteúdos and séries, most used first.
     */
    public function getUsage(): array
    {
        $rows = DB::table('tags')
            ->select('tags.id', 'tags.name')
            ->selectRaw('(select count(*) from subject_tag where subject_tag.tag_id = tags.id) as subjects_count')
            ->selectRaw('(select count(*) from resource_tag where resource_tag.tag_id = tags.id) as resources_count')
            ->selectRaw('(select count(*) from serie_tag where serie_tag.tag_id = tags.id) as series_count')
            ->get()
            ->map(function ($row) {
                $row->total = $row->subjects_count + $row->resources_count + $row->series_count;

                return $row;
            })
            ->sortByDesc('total')
            ->values();

        return $rows->all();
    }

    /**
     * Pairs of tags that are used together on the same verbete, conteúdo or série.
     */
    public function getCooccurrences(int $limit = 20): array
    {
        $names = Tag::pluck('name', 'id');
        $pairs = [];

        foreach ([['subject_tag', 'subject_id'], ['resource_tag', 'resource_id'], ['serie_tag', 'serie_id']] as [$table, $column]) {
            DB::table($table)->get(['tag_id', $column])->groupBy($column)->each(function ($rows) use (&$pairs) {
                $ids = $rows->pluck('tag_id')->unique()->sort()->values();

                for ($i = 0; $i < $ids->count(); $i++) {
                    for ($j = $i + 1; $j < $ids->count(); $j++) {
                        $key = $ids[$i] . '-' . $ids[$j];
                        $pairs[$key] = ($pairs[$key] ?? 0) + 1;
                    }
                }
            });
        }

        arsort($pairs);

        return collect($pairs)->take($limit)->map(function ($count, $key) use ($names) {
            [$a, $b] = explode('-', $key);

            return ['a' => $names[$a] ?? $a, 'b' => $names[$b] ?? $b, 'count' => $count];
        })->values()->all();
    }
}
