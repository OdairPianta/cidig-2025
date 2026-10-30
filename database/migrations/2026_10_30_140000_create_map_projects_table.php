<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Answers of the "Formulário de Mapeamento" of the Observatório. The collecting researcher's phone and
        // e-mail are deliberately not stored: they are in the spreadsheet, never on the platform.
        Schema::create('map_projects', function (Blueprint $table) {
            $table->id();
            $table->string('external_key', 40)->unique();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            // sheet = as typed, corrected = decimal point/degrees fixed on import, geocoded, manual
            $table->string('coordinates_source', 20)->nullable();
            $table->string('proposers')->nullable();
            $table->string('proposer_nature')->nullable();
            $table->text('purpose')->nullable();
            $table->text('mapping')->nullable();
            $table->text('description')->nullable();
            $table->text('nonhuman_influence')->nullable();
            $table->json('actants')->nullable();
            $table->json('tics')->nullable();
            $table->json('themes')->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('instagram', 2048)->nullable();
            $table->string('facebook', 2048)->nullable();
            $table->string('tiktok', 2048)->nullable();
            $table->boolean('has_own_app')->default(false);
            $table->string('app_url', 2048)->nullable();
            $table->string('researcher_name')->nullable();
            $table->string('source_language', 10)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_projects');
    }
};
