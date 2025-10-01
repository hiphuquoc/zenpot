<div class="categoryBox">

    @include('main.category.sort', [
        'language'          => $language ?? 'vi',
        'total'             => $total,
    ])

    @include('main.category.product')

    <!-- Pagination -->
    <div class="pagination">
        <button class="pagination-btn" disabled>
            <svg><use xlink:href="#icon_arrow_left"></use></svg>
        </button>
        <button class="pagination-btn active">1</button>
        <button class="pagination-btn">2</button>
        <button class="pagination-btn">3</button>
        <button class="pagination-btn">...</button>
        <button class="pagination-btn">8</button>
        <button class="pagination-btn">
            <svg><use xlink:href="#icon_arrow_right"></use></svg>
        </button>
    </div>
</div>