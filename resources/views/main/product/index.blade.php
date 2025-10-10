@extends('layouts.main')
@push('cssFirstView')
    <!-- trường hợp là local thì dùng vite để chạy npm run dev lúc code -->
    @if(env('APP_ENV')=='local')
        @vite('resources/sources/main/product-first-view.scss')
    @else
        @php
            $manifest           = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFirstView       = $manifest['resources/sources/main/product-first-view.scss']['file'];
        @endphp
        <style type="text/css">
            {!! file_get_contents(asset('build/' . $cssFirstView)) !!}
        </style>
    @endif
@endpush
@push('headCustom')
<!-- ===== START:: SCHEMA ===== -->
    <!-- STRAT:: Title - Description - Social -->
    @php
        $highPrice          = 0;
        $lowPrice           = $highPrice;
    @endphp
    @include('main.schema.social', ['item' => $item, 'lowPrice' => $lowPrice, 'highPrice' => $highPrice])
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: Title - Description - Social -->
    @include('main.schema.breadcrumb', compact('breadcrumb'))
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: Organization Schema -->
    @include('main.schema.organization')
    <!-- END:: Organization Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.article', compact('item'))
    <!-- END:: Article Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.creativeworkseries', compact('item'))
    <!-- END:: Article Schema -->

    <!-- STRAT:: FAQ Schema -->
    {{-- @include('main.schema.itemlist', ['data' => $blogs]) --}}
    <!-- END:: FAQ Schema -->

    <!-- STRAT:: FAQ Schema -->
    @include('main.schema.faq', ['data' => $itemSeo])
    <!-- END:: FAQ Schema -->
<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.categoryBlog.banner')
    
    <!-- thân trang -->
    <div class="pageContent container">

        <div class="mainContent">
        
            @include('main.product.body')
            
        </div>

    </div>

@endsection
@push('modal')
    {{-- <!-- Message Add to Cart -->
    <div id="js_addToCart_idWrite">
        @include('main.cart.cartMessage', [
            'title'     => null,
            'option'    => null,
            'quantity'  => 0,
            'price'     => 0,
            'image'     => null,
            'language'  => $language
        ])
    </div> --}}
@endpush
@push('bottom')
    <!-- Header bottom -->
    {{-- @include('main.snippets.headerBottom') --}}
    <!-- === START:: Zalo Ring === -->
    {{-- @include('main.snippets.zaloRing') --}}
    <!-- === END:: Zalo Ring === -->
@endpush
@push('scriptCustom')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // ===== 1. DỮ LIỆU ẢNH =====
            const images = [
                @foreach($item->prices as $price)
                    @foreach($price->files as $file)
                        @php
                            $imageSource = \App\Helpers\Image::getUrlImageCloud($file->file_path);
                        @endphp
                        "{{ $imageSource }}",
                    @endforeach
                @endforeach
            ];

            let currentImageIndex = 0;
            const mainImageEl = document.getElementById('mainImage');
            const zoomModal = document.getElementById('zoomModal');
            const zoomImage = document.getElementById('zoomImage');
            const quantityInput = document.getElementById('quantityInput');
            const priceElement = document.querySelector('.current-price');

            // ===== 2. GALLERY =====
            function setMainImage(index) {
                if (!images.length || !mainImageEl) return;
                currentImageIndex = index;
                mainImageEl.src = images[index];
                document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                    thumb.classList.toggle('active', i === index);
                });
            }

            function changeImage(direction) {
                if (!images.length) return;
                currentImageIndex += direction;
                if (currentImageIndex >= images.length) currentImageIndex = 0;
                if (currentImageIndex < 0) currentImageIndex = images.length - 1;
                setMainImage(currentImageIndex);
            }

            // ===== 3. ZOOM =====
            function openZoom() {
                if (!zoomModal || !zoomImage || !images.length) return;
                zoomImage.src = images[currentImageIndex];
                zoomModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeZoom() {
                if (!zoomModal) return;
                zoomModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Gắn sự kiện zoom nếu có ảnh chính
            if (mainImageEl) mainImageEl.addEventListener('click', openZoom);
            if (zoomModal) zoomModal.addEventListener('click', closeZoom);

            // ===== 4. THAY ĐỔI SỐ LƯỢNG =====
            function changeQuantity(change) {
                if (!quantityInput) return;
                let value = parseInt(quantityInput.value) + change;
                if (isNaN(value) || value < 1) value = 1;
                quantityInput.value = value;
            }

            // ===== 5. TÙY CHỌN SIZE =====
            const sizeOptions = document.querySelectorAll('.size-option');
            if (sizeOptions.length) {
                sizeOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        sizeOptions.forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            }

            // ===== 6. YÊU THÍCH (WISHLIST) =====
            const wishlistBtn = document.querySelector('.btn-icon');
            if (wishlistBtn) {
                wishlistBtn.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (!icon) return;

                    const isLiked = icon.classList.contains('fas');
                    if (isLiked) {
                        icon.classList.replace('fas', 'far');
                        this.style.color = '#e74c3c';
                        this.style.borderColor = '#e74c3c';
                        this.style.background = 'transparent';
                    } else {
                        icon.classList.replace('far', 'fas');
                        this.style.color = 'white';
                        this.style.background = '#e74c3c';
                    }

                    this.style.transform = 'scale(1.1)';
                    setTimeout(() => { this.style.transform = 'scale(1)'; }, 150);
                });
            }

            // ===== 7. SCROLL ANIMATION =====
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

            document.querySelectorAll('.details-section').forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(30px)';
                section.style.transition = 'all 0.6s ease';
                observer.observe(section);
            });

            // ===== 8. BÀN PHÍM GALLERY =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
                if (e.key === 'Escape') closeZoom();
            });

            // ===== 9. SWIPE GALLERY MOBILE =====
            const gallery = document.querySelector('.main-image-container');
            if (gallery) {
                let startX, endX;
                gallery.addEventListener('touchstart', e => startX = e.touches[0].clientX);
                gallery.addEventListener('touchend', e => {
                    endX = e.changedTouches[0].clientX;
                    const diff = startX - endX;
                    if (Math.abs(diff) > 50) {
                        diff > 0 ? changeImage(1) : changeImage(-1);
                    }
                });
            }

            // ===== 10. HIỆU ỨNG LOAD ẢNH =====
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('load', () => img.style.opacity = '1');
                if (img.complete) img.style.opacity = '1';
            });

            // ===== 11. GẮN SỰ KIỆN VÀO THUMBNAIL =====
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                thumb.addEventListener('click', () => setMainImage(i));
            });

            // ===== 12. THUMBNAIL VÀ GIÁ HIỂN THỊ THEO OPTION =====
            const options = document.querySelectorAll('.size-option');
            const priceSections = document.querySelectorAll('.price-section');
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('mainImage');

            // Tạo mảng lưu ảnh đầu tiên của mỗi price
            const priceFirstImages = [];
            const prices = @json(
                $item->prices->map(fn($price) =>
                    $price->files->map(fn($file) =>
                        \App\Helpers\Image::getUrlImageSmallByUrlImage($file->file_path)
                    )
                )
            );

            prices.forEach((files, i) => {
                if (files.length > 0) priceFirstImages[i] = files[0];
            });

            // Khi click chọn option
            options.forEach(option => {
                option.addEventListener('click', () => {
                    const index = option.getAttribute('data-price-index');

                    // Cập nhật active cho option
                    options.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');

                    // Hiển thị đúng phần giá
                    priceSections.forEach(section => {
                        section.classList.toggle('active', section.dataset.priceIndex === index);
                    });

                    // Đổi ảnh gallery sang ảnh đầu tiên của price tương ứng
                    if (priceFirstImages[index]) {
                        mainImage.src = priceFirstImages[index];
                        mainImage.dataset.src = priceFirstImages[index];
                    }

                    // Cập nhật active cho thumbnail
                    thumbnails.forEach(th => {
                        th.classList.toggle('active', th.dataset.priceIndex === index && th.dataset.fileIndex === "0");
                    });
                });
            });

            // Khởi tạo: ẩn giá khác, chỉ hiện giá đầu tiên
            priceSections.forEach((section, i) => {
                section.style.display = i === 0 ? 'flex' : 'none';
            });

            // Khi đổi option -> show/hide giá tương ứng
            options.forEach(option => {
                option.addEventListener('click', () => {
                    const index = option.dataset.priceIndex;
                    priceSections.forEach((section, i) => {
                        section.style.display = i == index ? 'flex' : 'none';
                    });
                });
            });
        });
    </script>
@endpush