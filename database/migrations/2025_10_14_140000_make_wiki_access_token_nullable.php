<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Users created from the admin panel have no wiki token; without a default the insert
    // fails on MySQL strict mode.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wiki_access_token')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wiki_access_token')->nullable(false)->default('')->change();
        });
    }
};
