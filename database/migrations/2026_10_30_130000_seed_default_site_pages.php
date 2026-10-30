<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the three pages the project asked for as empty drafts, so the admin only has to fill them in,
 * publish them and link them from the menu. Existing pages with the same address are left untouched.
 */
return new class extends Migration
{
    private const PAGES = [
        ['slug' => 'publicacoes', 'title' => 'Publicações', 'template' => 'publications'],
        ['slug' => 'observatorio', 'title' => 'Observatório Latino-americano de Cidadania Digital', 'template' => 'observatory'],
        ['slug' => 'estudos-de-caso', 'title' => 'Estudos de caso', 'template' => 'cases'],
    ];

    public function up(): void
    {
        foreach (self::PAGES as $page) {
            if (DB::table('pages')->where('slug', $page['slug'])->exists()) {
                continue;
            }

            DB::table('pages')->insert($page + ['is_published' => false, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Only drops the untouched drafts; pages that already have content or items stay.
        foreach (self::PAGES as $page) {
            $row = DB::table('pages')->where('slug', $page['slug'])->first();

            if ($row && ! $row->is_published && blank($row->body) && ! DB::table('page_items')->where('page_id', $row->id)->exists()) {
                DB::table('pages')->where('id', $row->id)->delete();
            }
        }
    }
};
