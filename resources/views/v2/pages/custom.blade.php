@extends('layouts.v2.app')

@section('content')
    <div id="custom-page" class="container content-home pt-5 pb-5">
        <h1 class="mb-3">{{ $page->title }}</h1>

        @if($page->summary)
            <p class="lead mb-4" style="white-space: pre-line">{{ $page->summary }}</p>
        @endif

        @if(filled($page->body))
            <div class="page-body mb-5">{!! $page->safe_body !!}</div>
        @endif

        @if($page->template === \App\Models\Page::TEMPLATE_PUBLICATIONS)
            @include('v2.pages.custom-publications')
        @elseif($page->hasItems())
            @include('v2.pages.custom-cards')
        @endif
    </div>
@endsection
