@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Nút Previous --}}
        @if ($paginator->onFirstPage())
            <button class="pagination-btn" disabled>
                <svg><use xlink:href="#icon_arrow_left"></use></svg>
            </button>
        @else
            <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <svg><use xlink:href="#icon_arrow_left"></use></svg>
            </a>
        @endif

        {{-- Số trang --}}
        @foreach ($elements as $element)
            {{-- Dấu … --}}
            @if (is_string($element))
                <button class="pagination-btn" disabled>{{ $element }}</button>
            @endif

            {{-- Link số trang --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button class="pagination-btn active">{{ $page }}</button>
                    @else
                        <a class="pagination-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Nút Next --}}
        @if ($paginator->hasMorePages())
            <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">
                <svg><use xlink:href="#icon_arrow_right"></use></svg>
            </a>
        @else
            <button class="pagination-btn" disabled>
                <svg><use xlink:href="#icon_arrow_right"></use></svg>
            </button>
        @endif
    </div>
@endif