<?php

use App\Models\Author;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Serie;
use App\Models\Sponsor;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->subject = Subject::create(['name' => 'Ética', 'slug' => 'etica']);
    $this->resource = Resource::create([
        'title' => 'Artigo', 'author' => 'Ana', 'key_words' => 'dados', 'description' => 'Texto',
        'publisher' => 'USP', 'language' => 'pt', 'source' => 'https://example.test/artigo',
        'format_id' => Resource::FORMAT_TEXT, 'subject_id' => $this->subject->id, 'created_by' => $this->user->id,
    ]);
});

it('uses the correct label and icons for each resource format', function (int $format, string $label, string $legacyIcon, string $modernIcon) {
    $resource = new Resource(['format_id' => $format]);
    expect($resource->getFormatDescription())->toBe($label)
        ->and($resource->getFormatIcon())->toBe($legacyIcon)
        ->and($resource->getFormatIcon(true))->toBe($format === Resource::FORMAT_TEXT ? 'fa fa-link' : $legacyIcon)
        ->and($resource->getNewFormatIcon())->toBe($modernIcon);
})->with([
    'text' => [1, 'Texto', 'far fa-file-alt', 'heroicon-o-document'],
    'video' => [2, 'Vídeo', 'fas fa-video', 'heroicon-o-video-camera'],
    'audio' => [3, 'Audio', 'fas fa-volume-up', 'heroicon-o-speaker-wave'],
    'image' => [4, 'Imagem', 'fas fa-image', 'heroicon-o-photo'],
]);

it('distinguishes linked content from an uploaded document and resolves its metadata', function () {
    expect($this->resource->hasUploadedFile())->toBeFalse()
        ->and($this->resource->getUploadedFileOriginalName())->toBe('https://example.test/artigo');

    $file = UploadedFile::create([
        'resource_id' => $this->resource->id, 'uploaded_by' => $this->user->id,
        'path' => 'documents/article.pdf', 'original_name' => 'Artigo original.pdf', 'mimeType' => 'application/pdf', 'size' => 120,
    ]);
    $this->resource->refresh();
    expect($this->resource->hasUploadedFile())->toBeTrue()
        ->and($this->resource->getUploadedFileOriginalName())->toBe('Artigo original.pdf')
        ->and($file->resource->is($this->resource))->toBeTrue()
        ->and($file->uploadedBy->is($this->user))->toBeTrue();
});

it('filters resources by any selected tag without duplicating a resource', function () {
    $a = Tag::create(['name' => 'Dados']);
    $b = Tag::create(['name' => 'Ética']);
    $other = Tag::create(['name' => 'Saúde']);
    $this->resource->tags()->attach([$a->id, $b->id]);

    expect($this->resource->isTagSelected($a->id))->toBeTrue()
        ->and($this->resource->isTagSelected($other->id))->toBeFalse()
        ->and(Resource::getByTagIds([$a->id, $b->id])->pluck('id')->all())->toBe([$this->resource->id])
        ->and(Resource::getByTagIds([$other->id])->count())->toBe(0)
        ->and(Resource::getByTagIds([])->count())->toBe(0);
});

it('resolves a credited authors resources and the inverse association', function () {
    $author = Author::findOrCreateByName('Ana Souza');
    $this->resource->authors()->attach($author);
    expect($author->resources->modelKeys())->toBe([$this->resource->id]);
});

it('adds each video to a series once and removes only its association', function () {
    $serie = Serie::create(['title' => 'Debates', 'slug' => 'debates', 'description' => 'Vídeos']);
    $video = Video::create(['video_id' => 'aaaaaaaaaaa', 'created_by' => $this->user->id]);
    $other = Video::create(['video_id' => 'bbbbbbbbbbb']);
    $serie->addVideo($other->id);
    $serie->addVideo($video->id);
    $serie->addVideo($video->id);
    $video->users()->attach($this->user);
    expect($serie->videos()->count())->toBe(2)
        ->and($video->series->modelKeys())->toBe([$serie->id])
        ->and($this->user->videos->modelKeys())->toBe([$video->id])
        ->and($video->createdBy->is($this->user))->toBeTrue();

    $serie->removeVideo($video->id);
    expect($serie->videos()->pluck('videos.id')->all())->toBe([$other->id])
        ->and(Video::find($video->id))->not->toBeNull();
});

it('resolves series partners tags and researchers in both directions', function () {
    $serie = Serie::create(['title' => 'Debates', 'slug' => 'debates', 'description' => 'Vídeos']);
    $sponsor = Sponsor::create(['name' => 'Parceiro']);
    $logo = $sponsor->logos()->create(['path' => 'partners/logo.png']);
    $tag = Tag::create(['name' => 'Dados']);
    $serie->sponsors()->attach($sponsor);
    $serie->tags()->attach($tag);
    $serie->users()->attach($this->user);

    expect($sponsor->series->modelKeys())->toBe([$serie->id])
        ->and($logo->sponsor->is($sponsor))->toBeTrue()
        ->and($tag->series->modelKeys())->toBe([$serie->id])
        ->and($this->user->series->modelKeys())->toBe([$serie->id]);
});

it('resolves post comments and their authors without mixing posts', function () {
    $post = Post::create(['title' => 'Debate', 'content' => 'Texto', 'user_id' => $this->user->id]);
    $other = Post::create(['title' => 'Outro', 'content' => 'Texto', 'user_id' => $this->user->id]);
    $comment = Comment::create(['content' => 'Comentário', 'post_id' => $post->id, 'user_id' => $this->user->id]);
    Comment::create(['content' => 'Outro comentário', 'post_id' => $other->id, 'user_id' => $this->user->id]);

    expect($post->comments->modelKeys())->toBe([$comment->id])
        ->and($comment->post->is($post))->toBeTrue()
        ->and($comment->user->is($this->user))->toBeTrue()
        ->and($this->user->comments()->count())->toBe(2);
});

it('distinguishes archived posts and resolves an optional cover image', function () {
    $post = new Post;
    expect($post->isArchived())->toBeFalse()->and($post->image_link)->toBeNull();
    $post->archived_at = now();
    $post->image_url = 'https://images.example.test/post.png';
    expect($post->isArchived())->toBeTrue()->and($post->image_link)->toBe('https://images.example.test/post.png');
});

it('resolves a publication item to its own page', function () {
    $page = Page::create(['title' => 'Livros', 'slug' => 'livros', 'template' => Page::TEMPLATE_PUBLICATIONS]);
    $item = $page->items()->create(['title' => 'Cidadania']);
    expect($item->page->is($page))->toBeTrue();
});
