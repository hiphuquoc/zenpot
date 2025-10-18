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
                @if(!empty($wallpaper->tags)&&$wallpaper->tags->isNotEmpty())
                    @foreach($wallpaper->tags as $tag)
                        @foreach($tag->infoTag->seos as $seo)
                            @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                                <div class="product-badge">{{ $seo->infoSeo->title }}</div>
                                @break;
                            @endif
                        @endforeach
                        @break;
                    @endforeach
                @endif
            </a>
            <div class="product-content">
                {{-- <!-- category main -->
                @if(!empty($wallpaper->categories)&&$wallpaper->categories->isNotEmpty())
                    @foreach($wallpaper->categories as $category)
                        @foreach($category->infoCategory->seos as $seo)
                            @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                                <div class="product-category">{{ $seo->infoSeo->title }}</div>
                                @break;
                            @endif
                        @endforeach
                        @break;
                    @endforeach
                @endif --}}
                <a href="/{{ $url }}"><h3 class="product-name maxLine_2">{{ $altImage }}</h3></a>
                {{-- <p class="product-description maxLine_3">{{ $description }}</p> --}}
                <div class="product-table">
                    <div class="product-table_item">
                        <svg><use xlink:href="#icon_expland"></use></svg>
                        <div>{{ $wallpaper->size ?? '--' }}</div>
                    </div>
                    <div class="product-table_item">
                        <svg><use xlink:href="#icon_weight-hanging"></use></svg>
                        <div>{{ $wallpaper->weight ?? '--' }} kg</div>
                    </div>
                </div>
                @if(!empty($wallpaper->categories)&&$wallpaper->categories->isNotEmpty())
                <!-- category list -->
                <div class="product-features">
                    @php
                        $maxShow = 4;
                    @endphp
                    @foreach($wallpaper->categories as $category)
                        @foreach($category->infoCategory->seos as $cSeo)
                            @if(!empty($cSeo->infoSeo->language)&&$cSeo->infoSeo->language==$language)
                                <span class="feature-tag">{{ $cSeo->infoSeo->title ?? '' }}</span>
                                @break;
                            @endif
                        @endforeach

                        @if(($loop->index + 1)==$maxShow)
                            @break;
                        @endif
                    @endforeach

                    @if(!empty($wallpaper->categories)&&$wallpaper->categories->count()>$maxShow)
                        <span class="feature-tag">...</span>
                    @endif
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
                        <span class="original-price">{{ $pMax }}</span>
                        <span class="current-price">{{ $pOrigin }}</span>
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