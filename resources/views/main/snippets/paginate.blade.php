@if(is_object($data) && method_exists($data, 'hasPages') && $data->hasPages())
    <nav class="pagination-container">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            <li class="page-item {{ $data->onFirstPage() ? 'disabled' : '' }}">
                @if ($data->onFirstPage())
                    <span class="page-link">«</span>
                @else
                    @if (!empty($urlSource))
                        <a class="page-link" href="{{ $urlSource }}?page={{ $data->currentPage() - 1 }}">«</a>
                    @else
                        <a class="page-link" href="{{ $data->previousPageUrl() }}" rel="prev">«</a>
                    @endif
                @endif
            </li>

            {{-- Pagination Elements --}}
            @php
                $currentPage = $data->currentPage();
                $lastPage = $data->lastPage();
                $range = 3; // Số trang hiển thị trước và sau trang hiện tại
                $start = max(1, $currentPage - $range);
                $end = min($lastPage, $currentPage + $range);

                // Điều chỉnh để luôn hiển thị đủ số trang nếu gần đầu hoặc cuối
                if ($currentPage <= $range + 1) {
                    $end = min($lastPage, $range * 2 + 1);
                }
                if ($currentPage >= $lastPage - $range) {
                    $start = max(1, $lastPage - $range * 2);
                }
            @endphp

            {{-- First Page --}}
            @if ($start > 1)
                <li class="page-item">
                    @if (!empty($urlSource))
                        <a class="page-link" href="{{ $urlSource }}?page=1">1</a>
                    @else
                        <a class="page-link" href="{{ $data->url(1) }}">1</a>
                    @endif
                </li>
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            {{-- Page Range --}}
            @foreach (range($start, $end) as $page)
                @if (!empty($urlSource))
                    <?php $customUrl = $urlSource . '?page=' . $page; ?>
                @else
                    <?php $customUrl = $data->url($page); ?>
                @endif
                <li class="page-item {{ $data->currentPage() == $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ $customUrl }}">{{ $page }}</a>
                </li>
            @endforeach

            {{-- Last Page --}}
            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item">
                    @if (!empty($urlSource))
                        <a class="page-link" href="{{ $urlSource }}?page={{ $lastPage }}">{{ $lastPage }}</a>
                    @else
                        <a class="page-link" href="{{ $data->url($lastPage) }}">{{ $lastPage }}</a>
                    @endif
                </li>
            @endif

            {{-- Next Page Link --}}
            <li class="page-item {{ !$data->hasMorePages() ? 'disabled' : '' }}">
                @if ($data->hasMorePages())
                    @if (!empty($urlSource))
                        <a class="page-link" href="{{ $urlSource }}?page={{ $data->currentPage() + 1 }}">»</a>
                    @else
                        <a class="page-link" href="{{ $data->nextPageUrl() }}" rel="next">»</a>
                    @endif
                @else
                    <span class="page-link">»</span>
                @endif
            </li>
        </ul>
    </nav>
@endif