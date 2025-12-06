@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex flex-column align-items-center">
        <ul class="pagination justify-content-center mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                    <span class="page-link rounded-circle">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-circle" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @elseif($page == $paginator->currentPage() - 1 || $page == $paginator->currentPage() + 1 || $page == 1 || $page == $paginator->lastPage())
                            {{-- show neighbor pages, first and last --}}
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif($page == 2 && $paginator->currentPage() > 4)
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        @elseif($page == $paginator->lastPage() - 1 && $paginator->currentPage() < $paginator->lastPage() - 3)
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-circle" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="page-link rounded-circle">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>

        {{-- Page Info --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                Hiển thị <strong>{{ $paginator->firstItem() }}</strong> đến <strong>{{ $paginator->lastItem() }}</strong>
                trong tổng số <strong>{{ $paginator->total() }}</strong> kết quả
            </small>
        </div>
    </nav>
@endif
