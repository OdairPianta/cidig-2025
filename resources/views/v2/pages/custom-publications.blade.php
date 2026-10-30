{{-- Publications grouped by type, newest first inside each group. --}}
@foreach($page->items->groupBy(fn ($item) => $item->category ?: 'Outras publicações') as $category => $items)
    <h2 class="mt-5 mb-3">{{ $category }}</h2>
    <ul class="list-unstyled">
        @foreach($items->sortByDesc('year') as $item)
            <li class="media mb-4">
                @if($item->image)
                    <img src="{{ \App\Support\Uploads::url($item->image) }}" alt="{{ $item->title }}" class="mr-3" style="width: 90px">
                @endif
                <div class="media-body">
                    <h5 class="mt-0 mb-1">
                        @if($item->url)
                            <a href="{{ $item->url }}" target="_blank" rel="noopener">{{ $item->title }}</a>
                        @else
                            {{ $item->title }}
                        @endif
                    </h5>
                    @if($item->authors || $item->year)
                        <p class="mb-1 text-muted">{{ collect([$item->authors, $item->year])->filter()->implode(' · ') }}</p>
                    @endif
                    @if($item->description)
                        <p class="mb-0" style="white-space: pre-line">{{ $item->description }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endforeach
