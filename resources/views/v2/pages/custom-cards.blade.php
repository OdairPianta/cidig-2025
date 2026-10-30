{{-- Cases and observatory entries as a card grid. --}}
<div class="row">
    @foreach($page->items as $item)
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                @if($item->image)
                    <img src="{{ \App\Support\Uploads::url($item->image) }}" alt="{{ $item->title }}" class="card-img-top">
                @endif
                <div class="card-body">
                    @if($item->category || $item->year)
                        <p class="small text-muted mb-1">{{ collect([$item->category, $item->year])->filter()->implode(' · ') }}</p>
                    @endif
                    <h5 class="card-title">{{ $item->title }}</h5>
                    @if($item->description)
                        <p class="card-text" style="white-space: pre-line">{{ $item->description }}</p>
                    @endif
                    @if($item->url)
                        <a href="{{ $item->url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                            {{ $page->template === \App\Models\Page::TEMPLATE_CASES ? 'Ver estudo de caso' : 'Saiba mais' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
