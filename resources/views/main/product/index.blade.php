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
    <!-- ZOOM MODAL -->
    <div id="zoomModal" class="zoom-modal" aria-hidden="true" role="dialog">
    <button type="button" class="zoom-close" aria-label="Close">&times;</button>
    <button type="button" class="zoom-nav prev" aria-label="Previous">&#10094;</button>
    <img id="zoomImage" class="zoom-image" src="" alt="Zoomed image">
    <button type="button" class="zoom-nav next" aria-label="Next">&#10095;</button>
    </div>

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
        document.addEventListener('DOMContentLoaded', function () {
            // Elements
            const mainImageEl = document.getElementById('mainImage');
            const zoomModal = document.getElementById('zoomModal');
            const zoomImage = document.getElementById('zoomImage');
            const galleryContainer = document.querySelector('.main-image-container');
            const sizeOptions = Array.from(document.querySelectorAll('.size-option'));
            const priceSections = Array.from(document.querySelectorAll('.price-section'));
            let thumbnailEls = Array.from(document.querySelectorAll('.thumbnail')); // array of div.thumbnail
            const mainGalleryPrev = document.querySelector('.main-gallery .gallery-nav.prev') || document.querySelector('.gallery-nav.prev');
            const mainGalleryNext = document.querySelector('.main-gallery .gallery-nav.next') || document.querySelector('.gallery-nav.next');
            const zoomPrev = document.querySelector('#zoomModal .zoom-nav.prev') || document.querySelector('.zoom-nav.prev');
            const zoomNext = document.querySelector('#zoomModal .zoom-nav.next') || document.querySelector('.zoom-nav.next');
            const zoomClose = document.querySelector('#zoomModal .zoom-close') || document.querySelector('.zoom-close');

            // State
            let currentThumb = document.querySelector('.thumbnail.active img') || document.querySelector('.thumbnail img') || null;
            let allGalleryImages = []; // list of data-src/src strings
            let currentZoomIndex = 0;
            const FADE_DELAY = 200; // ms (should be about half of CSS transition)

            // Helper: normalize image src (data-src preferred)
            function imgSrcOf(imgEl) {
                if (!imgEl) return '';
                return imgEl.getAttribute('data-src') || imgEl.getAttribute('src') || '';
            }

            // Helper: fade replace image element source
            function fadeReplaceImage(el, newSrc, delay = FADE_DELAY) {
                if (!el || !newSrc) return;
                const cur = el.getAttribute('src') || '';
                if (cur === newSrc) return;
                el.classList.add('fade-out');
                setTimeout(() => {
                    el.setAttribute('src', newSrc);
                    // also set data-src if element uses it (useful)
                    el.setAttribute('data-src', newSrc);
                    el.classList.remove('fade-out');
                }, delay);
            }

            // Ensure thumbnails list up-to-date (call when DOM changes dynamically)
            function refreshThumbnails() {
                thumbnailEls = Array.from(document.querySelectorAll('.thumbnail'));
            }

            // Set main image from a thumbnail <img> element
            function setMainImage(imgEl) {
                if (!imgEl || !mainImageEl) return;
                const newSrc = imgSrcOf(imgEl);
                if (!newSrc) return;

                // Fade main image
                fadeReplaceImage(mainImageEl, newSrc);

                // Update active thumb classes
                refreshThumbnails();
                thumbnailEls.forEach(t => t.classList.remove('active'));
                const parent = imgEl.closest('.thumbnail');
                if (parent) parent.classList.add('active');

                // update currentThumb reference
                currentThumb = imgEl;

                // If zoom is open, also update zoom image (sync)
                if (zoomModal && zoomModal.classList.contains('active')) {
                    // update allGalleryImages & currentZoomIndex
                    allGalleryImages = Array.from(document.querySelectorAll('.thumbnail img')).map(i => imgSrcOf(i));
                    currentZoomIndex = allGalleryImages.findIndex(s => s === newSrc);
                    if (currentZoomIndex === -1) currentZoomIndex = 0;
                    fadeReplaceImage(zoomImage, newSrc);
                }
            }

            // Init main image if none
            if (!currentThumb) {
                const firstThumbImg = document.querySelector('.thumbnail img');
                if (firstThumbImg) {
                    currentThumb = firstThumbImg;
                    // set initial main image to first thumb without heavy fade (direct)
                    const s = imgSrcOf(firstThumbImg);
                    if (s && mainImageEl) {
                        mainImageEl.src = s;
                        mainImageEl.dataset.src = s;
                        firstThumbImg.closest('.thumbnail')?.classList.add('active');
                    }
                }
            }

            // Click on thumbnail (listen on thumbnail div so click anywhere counts)
            refreshThumbnails();
            thumbnailEls.forEach(thumb => {
                thumb.addEventListener('click', (e) => {
                    const img = thumb.querySelector('img');
                    if (img) setMainImage(img);
                });
            });

            // Options: select size/price -> activate price-section and set main image to first thumb of that price
            if (sizeOptions.length) {
                // initial display state for price sections: show only active
                priceSections.forEach((sec, i) => {
                    sec.style.display = sec.classList.contains('active') ? 'flex' : 'none';
                });

                sizeOptions.forEach(opt => {
                    opt.addEventListener('click', () => {
                        const priceIndex = opt.getAttribute('data-price-index');

                        // Active option UI
                        sizeOptions.forEach(o => o.classList.remove('active'));
                        opt.classList.add('active');

                        // Show/hide price sections (display) + toggle active class
                        priceSections.forEach(sec => {
                            const match = sec.getAttribute('data-price-index') === priceIndex;
                            sec.classList.toggle('active', match);
                            sec.style.display = match ? 'flex' : 'none';
                        });

                        // Find first thumbnail of this price-index and set main image
                        const firstThumb = document.querySelector(`.thumbnail[data-price-index="${priceIndex}"]`);
                        if (firstThumb) {
                            const img = firstThumb.querySelector('img');
                            if (img) setMainImage(img);
                        }
                    });
                });
            }

            // Open Zoom modal
            function openZoom() {
                if (!zoomModal || !zoomImage || !mainImageEl) return;
                // build gallery list
                allGalleryImages = Array.from(document.querySelectorAll('.thumbnail img')).map(i => imgSrcOf(i));
                const cur = mainImageEl.dataset.src || mainImageEl.src || '';
                currentZoomIndex = allGalleryImages.findIndex(s => s === cur);
                if (currentZoomIndex === -1) currentZoomIndex = 0;
                // set zoom image and show
                zoomImage.src = allGalleryImages[currentZoomIndex] || cur;
                zoomModal.classList.add('active');
                zoomModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            // Close zoom
            function closeZoom() {
                if (!zoomModal) return;
                zoomModal.classList.remove('active');
                zoomModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            // Change image inside zoom (and sync main gallery)
            function zoomChangeImage(direction) {
                if (!allGalleryImages.length) {
                    allGalleryImages = Array.from(document.querySelectorAll('.thumbnail img')).map(i => imgSrcOf(i));
                }
                if (!allGalleryImages.length) return;
                currentZoomIndex = (currentZoomIndex + direction + allGalleryImages.length) % allGalleryImages.length;
                const newSrc = allGalleryImages[currentZoomIndex];

                // try to find the thumbnail img element that matches newSrc
                const thumbImgs = Array.from(document.querySelectorAll('.thumbnail img'));
                const match = thumbImgs.find(i => imgSrcOf(i) === newSrc);

                if (match) {
                    // setMainImage will also update zoomImage if modal active
                    setMainImage(match);
                } else {
                    // fallback: just update zoomImage
                    fadeReplaceImage(zoomImage, newSrc);
                }
            }

            // Events: open zoom on main image click
            if (mainImageEl) mainImageEl.addEventListener('click', openZoom);

            // Zoom controls
            if (zoomClose) zoomClose.addEventListener('click', closeZoom);
            if (zoomPrev) {
                zoomPrev.addEventListener('click', (e) => {
                    e.stopPropagation();
                    zoomChangeImage(-1);
                });
            }
            if (zoomNext) {
                zoomNext.addEventListener('click', (e) => {
                    e.stopPropagation();
                    zoomChangeImage(1);
                });
            }

            // Click background to close (but not clicks on inner content)
            if (zoomModal) {
                zoomModal.addEventListener('click', (e) => {
                    if (e.target === zoomModal) closeZoom();
                });
            }

            // Next / Prev for main gallery (outside zoom)
            function changeImage(direction) {
                // rebuild thumbs list
                const thumbsImgs = Array.from(document.querySelectorAll('.thumbnail img')).filter(Boolean);
                if (!thumbsImgs.length) return;
                let index = thumbsImgs.indexOf(currentThumb);
                if (index === -1) index = 0;
                index = (index + direction + thumbsImgs.length) % thumbsImgs.length;
                const newImg = thumbsImgs[index];
                if (newImg) setMainImage(newImg);
            }

            if (mainGalleryPrev) mainGalleryPrev.addEventListener('click', () => changeImage(-1));
            if (mainGalleryNext) mainGalleryNext.addEventListener('click', () => changeImage(1));

            // Swipe mobile for main gallery
            if (galleryContainer) {
                let startX = 0;
                galleryContainer.addEventListener('touchstart', e => startX = e.touches[0].clientX);
                galleryContainer.addEventListener('touchend', e => {
                    const diff = startX - e.changedTouches[0].clientX;
                    if (Math.abs(diff) > 50) diff > 0 ? changeImage(1) : changeImage(-1);
                });
            }

            // Keyboard handling: arrows for gallery or zoom, Esc closes zoom
            document.addEventListener('keydown', (e) => {
                if (zoomModal && zoomModal.classList.contains('active')) {
                    if (e.key === 'ArrowLeft') zoomChangeImage(-1);
                    if (e.key === 'ArrowRight') zoomChangeImage(1);
                    if (e.key === 'Escape') closeZoom();
                } else {
                    if (e.key === 'ArrowLeft') changeImage(-1);
                    if (e.key === 'ArrowRight') changeImage(1);
                }
            });

            // Soft image-load effect: set opacity to 1 when loaded
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('load', () => img.style.opacity = '1');
                if (img.complete) img.style.opacity = '1';
            });

            // Ensure initial price-section display (if JS was loaded after HTML)
            priceSections.forEach(sec => {
                sec.style.display = sec.classList.contains('active') ? 'flex' : 'none';
            });
        });
        </script>

@endpush