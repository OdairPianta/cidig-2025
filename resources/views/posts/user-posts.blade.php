@extends('layouts.v2.app')

@push('css')
<link href="{{ asset('css/blog.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div id="user-posts-page" class="container content-home pt-5 pb-5 p-0">
        <!-- Informações do pesquisador -->
        <div id="team" class="content-team container content-home pt-5 pb-5 p-0">
            <div class="container pt-5 pb-5">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <h1 class="mb-4">Blog do Pesquisador</h1>

                        <div class="col-12 mb-5">
                            <div id="coordinator-container" class="row mt-2">
                                <div class="col-xs-12 col-sm-6">
                                    @if($user->getPictureLink())
                                        <img src="{{ $user->getPictureLink() }}"
                                             alt="{{ $user->name }}"
                                             class="mr-2"
                                        />
                                    @endif
                                </div>

                                <div class="col-xs-12 col-sm-6">
                                    <div class="media-body">
                                        <h5>Pesquisador</h5>
                                        <h3 class="mt-4">{{ $user->name }}</h3>
                                        @if($user->information && $user->information->minibio)
                                            <p class="mt-0 description">{{ $user->information->minibio }}</p>
                                        @endif
                                    </div>
                                    <p>
                                        <a href="{{ route('v2.authorPage', Str::slug($user->name)) }}">
                                            Ver perfil completo...
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegação -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('posts.index') }}">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                    </ol>
                </nav>
                <h2 class="mb-3">Posts de {{ $user->name }}</h2>
            </div>
        </div>

        @if (count($posts))
            <div class="row" id="user-posts-list">
                <div class="col-12">
                    <div class="card-columns">
                        @foreach($posts->items() as $post)
                            <div class="card mb-4">
                                @if($post->image_url)
                                    <img class="card-img-top" src="{{ $post->image_link }}" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                                @endif
                                
                                <div class="card-header">
                                    <small class="text-muted">
                                        Publicado em {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>

                                <div class="card-body">
                                    <div class="text-truncate1">
                                        <h5 class="card-title">
                                            <i class="fas fa-blog"></i> {{ $post->title }}
                                        </h5>
                                    </div>

                                    <div class="card-text">
                                        <p>{{ $post->excerpt }}</p>
                                    </div>

                                    <div class="float-right">
                                        <a href="{{ route('post.show', [Str::slug($user->name), $post->slug]) }}" class="btn-detail btn btn-outline-info btn-sm">
                                            Ler post completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row d-flex justify-content-center">
                {{ $posts->render() }}
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <p class="text-center">
                        Este pesquisador ainda não publicou nenhum post.
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection