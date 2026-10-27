<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('name');
            $table->string('email')->nullable()->after('contact_name');
            $table->string('landline', 30)->nullable()->after('email');
            $table->string('mobile', 30)->nullable()->after('landline');
            $table->text('address')->nullable()->after('mobile');
        });
    }

    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'email', 'landline', 'mobile', 'address']);
        });
    }
};
