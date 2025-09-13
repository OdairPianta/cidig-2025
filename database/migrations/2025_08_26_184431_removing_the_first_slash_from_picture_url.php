<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE user_informations
            SET picture_url = SUBSTRING(picture_url, 2)
            WHERE picture_url LIKE '/%';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opcional: voltar ao estado original (recolocar a barra no início)
        DB::statement("
            UPDATE user_informations
            SET picture_url = CONCAT('/', picture_url)
            WHERE picture_url NOT LIKE '/%';
        ");
    }
};
