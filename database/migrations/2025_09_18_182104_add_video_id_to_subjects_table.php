<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('video_id')->nullable();
            
            // Adiciona a foreign key constraint
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['video_id']); // Remove a foreign key primeiro
            $table->dropColumn('video_id');
        });
    }
};