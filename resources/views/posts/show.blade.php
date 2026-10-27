@extends('layouts.v2.app')

@section('title', $post->title)

@push('css')
<link href="{{ asset('css/blog.css') }}" rel="stylesheet">
@endpush

@section('content')
<div id="post-page" class="container content-home pt-5 pb-5">
    <div class="row">
        <div class="col-12">
            <!-- Navegação -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('posts.index') }}">Blog</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.posts', Str::slug($user->name)) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
                </ol>
            </nav>

            <!-- Cabeçalho do post -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if($user->getPictureLink())
                                <img src="{{ $user->getPictureLink() }}"
                                     alt="{{ $user->name }}"
                                     class="team-person-picture rounded-circle"
                                     style="height: 3em;width: 3em;"
                                >
                            @endif
                        </div>
                        <div class="col">
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">
                                Publicado em {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i') }}
                            </small>
                            @if($post->authors->isNotEmpty())
                                <div class="small">Autoria: {{ $post->byline }}</div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('v2.authorPage', Str::slug($user->name)) }}" class="btn btn-outline-info btn-sm">
                                Ver perfil do autor
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h1 class="mb-4">{{ $post->title }}</h1>
                    
                    @if($post->image_url)
                        <div class="text-center mb-4">
                            <img src="{{ $post->image_link }}" 
                                 alt="{{ $post->title }}" 
                                 class="img-fluid rounded"
                                 style="max-height: 400px; width: auto;">
                        </div>
                    @endif
                    
                    <div class="post-content">
                        {!! $post->safe_content !!}
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('user.posts', Str::slug($user->name)) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Outros posts deste autor
                            </a>
                        </div>
                        <div class="col-6 text-right">
                            <a href="{{ route('posts.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-blog"></i> Ver todos os posts
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do autor -->
            <div class="card">
                <div class="card-header">
                    <h5>Sobre o autor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            @if($user->getPictureLink())
                                <img src="{{ $user->getPictureLink() }}"
                                     alt="{{ $user->name }}"
                                     class="team-person-picture rounded-circle"
                                     style="height: 4em;width: 4em;"
                                >
                            @endif
                        </div>
                        <div class="col">
                            <h6>{{ $user->name }}</h6>
                            @if($user->information && $user->information->minibio)
                                <p class="text-muted">{{ Str::limit($user->information->minibio, 200) }}</p>
                            @endif
                            <div class="mt-2">
                                <a href="{{ route('v2.authorPage', Str::slug($user->name)) }}" class="btn btn-outline-primary btn-sm">
                                    Ver perfil completo
                                </a>
                                <a href="{{ route('user.posts', Str::slug($user->name)) }}" class="btn btn-outline-secondary btn-sm">
                                    Outros posts
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('inline_scripts')
<script>
    document.querySelectorAll('.post-content a[href]').forEach(function (link) {
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener noreferrer');
    });
</script>
@endsection
