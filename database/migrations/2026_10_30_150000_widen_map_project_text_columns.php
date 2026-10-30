<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The first real import stopped after 3 projects: "proponentes" go up to ~900 characters in the sheet
    // and MySQL rejected them in a 255 column (SQLite, used by the tests, does not enforce the length).
    public function up(): void
    {
        Schema::table('map_projects', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('country')->nullable()->change();
            $table->text('city')->nullable()->change();
            $table->text('proposers')->nullable()->change();
            $table->text('proposer_nature')->nullable()->change();
            $table->text('researcher_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Left as text: shrinking would truncate or fail on imported answers.
    }
};
