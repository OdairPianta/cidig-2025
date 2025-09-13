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
        Schema::create('user_informations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index();
            $table->foreignId('university_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->string('picture_url')->nullable();
            $table->string('lattes_url')->nullable();
            $table->string('orcid_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('minibio')->nullable();
            $table->text('description')->nullable();
            $table->string('title')->nullable();
            $table->text('roles')->nullable();
            $table->string('website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_informations');
    }
};
