@extends('layouts.v2.app')

@section('content')
    {{-- The site is dark (#090E1F), so text colours are set here instead of inheriting Bootstrap's dark-on-light defaults. --}}
    <style>
        body#app-2023 #custom-page { color: #F1F5F9; }
        body#app-2023 #custom-page h1, body#app-2023 #custom-page h2, body#app-2023 #custom-page h3, body#app-2023 #custom-page h4, body#app-2023 #custom-page h5 { color: #FFFFFF; text-align: left; }
        body#app-2023 #custom-page h2 { font-size: 1.75rem; }
        body#app-2023 #custom-page p, body#app-2023 #custom-page li, body#app-2023 #custom-page td, body#app-2023 #custom-page th, body#app-2023 #custom-page blockquote { color: #F1F5F9; }
        body#app-2023 #custom-page .lead { color: #CBD5E1; }
        body#app-2023 #custom-page .text-muted, body#app-2023 #custom-page .small { color: #94A3B8 !important; }
        body#app-2023 #custom-page a:not(.btn) { color: #FF8A5C; text-decoration: underline; }
        body#app-2023 #custom-page a:not(.btn):hover { color: #FFB08F; }
        body#app-2023 #custom-page blockquote { border-left: 3px solid #FF4D00; padding-left: 1rem; }
        body#app-2023 #custom-page table { color: #F1F5F9; }
        body#app-2023 #custom-page th, body#app-2023 #custom-page td { border-color: rgba(255, 255, 255, .2); }
        body#app-2023 #custom-page .card { background-color: #161C32; border: 1px solid rgba(255, 255, 255, .1); color: #F1F5F9; }
        body#app-2023 #custom-page .card-title { color: #FFFFFF; }
        body#app-2023 #custom-page .card-text { color: #F1F5F9; }
        body#app-2023 #custom-page .btn-outline-primary { color: #FF8A5C; border-color: #FF8A5C; }
        body#app-2023 #custom-page .btn-outline-primary:hover { color: #090E1F; background-color: #FF8A5C; border-color: #FF8A5C; }
    </style>

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
