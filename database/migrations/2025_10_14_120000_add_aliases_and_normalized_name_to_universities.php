<?php

use App\Models\University;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->json('aliases')->nullable()->after('name');
            $table->string('normalized_name')->nullable()->index()->after('aliases');
        });

        University::query()->each(function (University $university) {
            $university->forceFill(['normalized_name' => University::normalize($university->name)])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn(['aliases', 'normalized_name']);
        });
    }
};
