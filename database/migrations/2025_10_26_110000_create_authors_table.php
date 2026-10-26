<?php

use App\Models\Author;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->timestamps();
        });

        Schema::create('author_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->unique(['author_id', 'resource_id']);
        });

        // Existing free-text authors ("Ana; Bruno") become author records linked to their content.
        DB::table('resources')->select('id', 'author')->orderBy('id')->each(function ($resource) {
            foreach (preg_split('/\s*[;\n]\s*/', (string) $resource->author) as $name) {
                if (trim($name) === '') {
                    continue;
                }

                $author = Author::findOrCreateByName($name);

                DB::table('author_resource')->insertOrIgnore([
                    'author_id' => $author->id,
                    'resource_id' => $resource->id,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_resource');
        Schema::dropIfExists('authors');
    }
};
