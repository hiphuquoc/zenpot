@php
    $dataSlider = [
        [
            'src'   => 'https://zenpot.storage.googleapis.com/storage/images/background-slider-zenpot-1.webp',
        ],
        [
            'src'   => 'https://zenpot.storage.googleapis.com/storage/images/background-slider-zenpot-2.webp',
        ],
         [
            'src'   => 'https://zenpot.storage.googleapis.com/storage/images/background-slider-zenpot-3.webp',
        ],
    ];
@endphp
<!-- START: Home slider Desktop -->
<div id="js_lazyloadSliderDesktop_box" class="swiper sliderHome">
    <div class="swiper-wrapper">
        @foreach($dataSlider as $slider)
            <div class="swiper-slide sliderHome_item" style="background:url({{ $slider['src'] }})">
                <div class="containerSlider">
                    <div class="sliderHome_item_content">
                        <!-- tiêu đề typing -->
                        <div class="sliderHome_item_content_title2">
                            Giải pháp<br/>chậu bonsai & decor<br/>sang trọng
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination dots -->
    <div class="swiper-pagination"></div>

    <!-- Navigation arrows -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    
</div>
<!-- END: Home slider Desktop -->
@push('headCustom')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
@endpush
@push('scriptCustom')
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            
            // Init Swiper
            const swiper = new Swiper('.sliderHome', {
                loop: true,
                autoplay: {
                    delay: 15000,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    0: {
                        navigation: false,
                    },
                    568: {
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        }
                    }
                }
            });
            
        });
        
    </script>
@endpush