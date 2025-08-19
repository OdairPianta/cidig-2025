<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->string('image')->nullable()->after('description');
            $table->string('background-image')->nullable()->after('image');
            $table->string('background-credit')->nullable()->after('background-image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('description');
            $table->dropColumn('image');
            $table->dropColumn('background-image');
            $table->dropColumn('background-credit');
        });
    }
};
