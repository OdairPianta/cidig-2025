<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series_videos', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
        });

        // Episodes keep the order they were attached in, and inherit the video's own title/description.
        $position = [];
        DB::table('series_videos')->orderBy('created_at')->orderBy('video_id')->get()->each(function ($row) use (&$position) {
            $position[$row->serie_id] = ($position[$row->serie_id] ?? 0) + 1;
            $video = DB::table('videos')->where('id', $row->video_id)->first();

            DB::table('series_videos')
                ->where('serie_id', $row->serie_id)
                ->where('video_id', $row->video_id)
                ->update([
                    'position' => $position[$row->serie_id],
                    'title' => $video->title ?? null,
                    'description' => $video->description ?? null,
                ]);
        });

        // The form had two fields for the thumbnail and two for the background; the site showed the
        // "new" one when present, so that one becomes the single value.
        DB::table('series')->whereNotNull('thumbnail_novo')->update(['thumbnail' => DB::raw('thumbnail_novo')]);
        DB::table('series')->whereNotNull('background_img')->update(['background_image' => DB::raw('background_img')]);
        DB::table('series')->update(['thumbnail_novo' => null, 'background_img' => null]);
    }

    public function down(): void
    {
        Schema::table('series_videos', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'position']);
        });
    }
};
