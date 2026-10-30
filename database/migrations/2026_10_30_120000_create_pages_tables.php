<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template', 30)->default('text');
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('page_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('authors')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('menu', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->after('link')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('page_id');
        });

        Schema::dropIfExists('page_items');
        Schema::dropIfExists('pages');
    }
};
