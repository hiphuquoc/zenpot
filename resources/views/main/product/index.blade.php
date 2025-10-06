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
        // Image gallery data
        const images = [
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-4.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-2.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-3.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-1.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-5.webp',

            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-21.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-22.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-23.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-24.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-25.webp',
            'https://zenpot.storage.googleapis.com/storage/images/anh-san-pham-26.webp',
        ];

        let currentImageIndex = 0;

        // Set main image
        function setMainImage(index) {
            currentImageIndex = index;
            document.getElementById('mainImage').src = images[index];
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        // Change image with navigation
        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex >= images.length) currentImageIndex = 0;
            if (currentImageIndex < 0) currentImageIndex = images.length - 1;
            setMainImage(currentImageIndex);
        }

        // Zoom functionality
        function openZoom() {
            const modal = document.getElementById('zoomModal');
            const zoomImg = document.getElementById('zoomImage');
            zoomImg.src = images[currentImageIndex];
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeZoom() {
            const modal = document.getElementById('zoomModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Quantity controls
        function changeQuantity(change) {
            const input = document.getElementById('quantityInput');
            let value = parseInt(input.value) + change;
            if (value < 1) value = 1;
            input.value = value;
        }

        // Size selection
        document.addEventListener('DOMContentLoaded', function() {
            // Size option selection
            document.querySelectorAll('.size-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Wishlist functionality
            document.querySelector('.btn-icon').addEventListener('click', function() {
                const icon = this.querySelector('i');
                const isLiked = icon.classList.contains('fas');
                
                if (isLiked) {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.style.color = '#e74c3c';
                    this.style.borderColor = '#e74c3c';
                } else {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.style.color = 'white';
                    this.style.background = '#e74c3c';
                }
                
                // Animation
                this.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });

            // Smooth scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all sections for animation
            document.querySelectorAll('.details-section').forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(30px)';
                section.style.transition = 'all 0.6s ease';
                observer.observe(section);
            });

            // Keyboard navigation for gallery
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    changeImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeImage(1);
                } else if (e.key === 'Escape') {
                    closeZoom();
                }
            });

            // Touch/swipe support for mobile gallery
            let startX, endX;
            const gallery = document.querySelector('.main-image-container');
            
            gallery.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
            });
            
            gallery.addEventListener('touchend', function(e) {
                endX = e.changedTouches[0].clientX;
                const diff = startX - endX;
                
                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        changeImage(1); // Swipe left - next image
                    } else {
                        changeImage(-1); // Swipe right - previous image
                    }
                }
            });

            // Image lazy loading effect
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
                
                if (img.complete) {
                    img.style.opacity = '1';
                }
            });

            // Price animation on load
            const priceElement = document.querySelector('.current-price');
            const targetPrice = 890000;
            let currentPrice = 0;
            const increment = targetPrice / 100;
            const duration = 2000; // 2 seconds
            const intervalTime = duration / 100;

            const priceInterval = setInterval(() => {
                currentPrice += increment;
                if (currentPrice >= targetPrice) {
                    currentPrice = targetPrice;
                    clearInterval(priceInterval);
                }
                priceElement.textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(currentPrice);
            }, intervalTime);
        });
    </script>
@endpush