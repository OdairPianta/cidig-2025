<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('source_author')->nullable()->after('video_id');
            $table->string('source_title')->nullable()->after('source_author');
            $table->string('source_channel')->nullable()->after('source_title');
            $table->unsignedSmallInteger('source_year')->nullable()->after('source_channel');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['source_author', 'source_title', 'source_channel', 'source_year']);
        });
    }
};
