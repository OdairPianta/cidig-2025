@extends('layouts.v2.app')

@section('content')
    <div id="series-index" class="container content-home pt-5 pb-5 p-0">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Webséries</h1>
                
                <div class="row">
                    @foreach($series as $serie)
                        <div class="col-sm-6 col-md-4 mb-4">
                            <div class="card card-body p-0">
                                <a class="access-link" href="{{ route('v2.series.show', $serie->slug) }}">
                                    @if($serie->thumbnail_novo)
                                        <img src="{{ url($serie->thumbnail_novo) }}" alt="{{ $serie->title }}" class="img-fluid">
                                    @elseif($serie->thumbnail)
                                        <img src="{{ url($serie->thumbnail) }}" alt="{{ $serie->title }}" class="img-fluid">
                                    @else
                                        <img src="{{ url('/images/default.jpg') }}" alt="{{ $serie->title }}" class="img-fluid">
                                    @endif
                                </a>
                                
                                <div class="card-body">
                                    <h5 class="card-title">{{ $serie->title }}</h5>
                                    <p class="card-text">{{ Str::limit($serie->description, 100) }}</p>
                                    
                                    @if($serie->tags->count())
                                        <div class="tags-list mt-2">
                                            @foreach($serie->tags as $tag)
                                                <a href="{{ route('v2.searchByTag', $tag->id) }}" class="label">
                                                    {{ $tag->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('v2.series.show', $serie->slug) }}" class="btn btn-primary mt-3">
                                        Acessar Websérie
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($series->isEmpty())
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            Nenhuma websérie encontrada.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection