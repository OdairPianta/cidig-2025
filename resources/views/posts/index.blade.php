@extends('layouts.v2.app')

@section('content')
    <div id="posts-page" class="container content-home pt-5 pb-5 p-0">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-4">Blog dos Pesquisadores</h1>
                <p class="lead">Acompanhe as reflexões e insights dos nossos pesquisadores sobre Cidadania Digital</p>
            </div>
        </div>

        @if (count($posts))
            <div class="row" id="posts-list">
                <div class="col-12">
                    <div class="card-columns">
                        @foreach($posts->items() as $post)
                            <div class="card mb-4">
                                @if($post->image_url)
                                    <img class="card-img-top" src="{{ Storage::url($post->image_url) }}" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                                @endif
                                
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-9">
                                            @if($post->user->getPictureLink())
                                                <img src="{{ $post->user->getPictureLink() }}"
                                                     alt="{{ $post->user->name }}"
                                                     class="team-person-picture rounded-circle mr-2"
                                                     style="height: 2em;width: 2em;"
                                                     title="{{ $post->user->name }}"
                                                >
                                            @endif

                                            <small class="person">
                                                {{ $post->user->name }}
                                            </small>
                                        </div>
                                        <div class="col-xs-12 col-sm-3 text-right">
                                            <small class="text-muted">
                                                {{ $post->published_at ? $post->published_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="text-truncate1">
                                        <h5 class="card-title">
                                            <i class="fas fa-blog"></i> {{ $post->title }}
                                        </h5>
                                    </div>

                                    <div class="card-text">
                                        <p>{{ Str::limit(strip_tags($post->content), 150) }}</p>
                                    </div>

                                    <div class="float-right">
                                        <a href="{{ route('post.show', [Str::slug($post->user->name), $post->slug]) }}" class="btn-detail btn btn-outline-info btn-sm">
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
                        Nenhum post publicado ainda.
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection