<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index()->after('name');
        });

        Tag::query()->each(function (Tag $tag) {
            $tag->forceFill(['normalized_name' => Tag::normalize($tag->name)])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('normalized_name');
        });
    }
};
