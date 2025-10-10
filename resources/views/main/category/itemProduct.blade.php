<!-- Product 1 -->
@foreach($wallpaper->seos as $seo)
    @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
        @php
            $altImage       = $seo->infoSeo->title ?? '';
            $description    = $seo->infoSeo->seo_description ?? '';
            $url            = $seo->infoSeo->slug_full ?? '';
        @endphp
        <div class="product-card">
            <a href="/{{ $url }}" class="product-image">
                @foreach($wallpaper->prices as $price)
                    @foreach($price->files as $file)
                        @php
                            /* lấy ảnh Small */
                            $imageMini  = \App\Helpers\Image::getUrlImageMiniByUrlImage($file->file_path);
                            $imageLarge = \App\Helpers\Image::getUrlImageLargeByUrlImage($file->file_path);
                        @endphp     
                        <img class="lazyload" src="{{ $imageMini }}" data-src="{{ $imageLarge }}" alt="{{ $altImage }}" title="{{ $altImage }}" loading="lazy" />
                        @break;
                    @endforeach
                    @break;
                @endforeach
                <!-- tag -->
                <div class="product-badge">Bán chạy</div>
            </a>
            <div class="product-content">
                <!-- category main -->
                <div class="product-category">Chậu để bàn</div>
                <a href="/{{ $url }}"><h3 class="product-name maxLine_2">{{ $altImage }}</h3></a>
                <p class="product-description maxLine_3">{{ $description }}</p>
                @if(!empty($wallpaper->categories)&&$wallpaper->categories->isNotEmpty())
                <!-- category list -->
                <div class="product-features">
                    @foreach($wallpaper->categories as $category)

                        @foreach($category->infoCategory->seos as $cSeo)
                            @if(!empty($cSeo->infoSeo->language)&&$cSeo->infoSeo->language==$language)
                                <span class="feature-tag">{{ $cSeo->infoSeo->title ?? '' }}</span>
                                @break;
                            @endif
                        @endforeach

                        @if($loop->index==3)
                            @break;
                        @endif
                    @endforeach
                </div>
                @endif
                <div class="product-footer">
                    @php
                        // giá gạch bỏ
                        $pMax               = !empty($wallpaper->price) ? \App\Helpers\Number::getFormatPriceByLanguage($wallpaper->price, $language) : '--';
                        // giá bán thức
                        $pOrigin            = '--';
                        foreach($wallpaper->prices as $p){
                            $pOrigin        = \App\Helpers\Number::getFormatPriceByLanguage($p->price, $language);
                            break;
                        }
                    @endphp
                    <div class="product-price">
                        <span class="current-price">{{ $pOrigin }}</span>
                        <span class="original-price">{{ $pMax }}</span>
                    </div>
                    <!-- add to cart -->
                    <button class="add-to-cart" onclick="setMessageModal('{{ config('data_language_1.'.$language.'.notice_construction_create_post_title') }}', '{{ config('data_language_1.'.$language.'.notice_construction_create_post_body') }}');">
                        <svg><use xlink:href="#icon_cart"></use></svg>
                        Thêm vào giỏ
                    </button>
                </div>
            </div>
        </div>
        @break;
    @endif
@endforeach