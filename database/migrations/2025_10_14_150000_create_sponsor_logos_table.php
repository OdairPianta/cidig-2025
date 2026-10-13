<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_logos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Existing single logos become the active variant.
        DB::table('sponsors')->whereNotNull('image')->where('image', '!=', '')->orderBy('id')->each(function ($sponsor) {
            DB::table('sponsor_logos')->insert([
                'sponsor_id' => $sponsor->id,
                'path' => $sponsor->image,
                'label' => 'Logo atual',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_logos');
    }
};
