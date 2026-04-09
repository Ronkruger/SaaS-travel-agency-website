@if ($paginator->hasPages())
<nav class="admin-pagination" role="navigation" aria-label="Pagination Navigation">
    <div class="admin-pagination-info">
        Showing
        @if ($paginator->firstItem())
            <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
        @else
            0
        @endif
        of <strong>{{ $paginator->total() }}</strong> results
    </div>

    <ul class="admin-pagination-list">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>&laquo; Previous</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}">&laquo; Previous</a></li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}">Next &raquo;</a></li>
        @else
            <li class="disabled"><span>Next &raquo;</span></li>
        @endif
    </ul>
</nav>
@endif
