<div class="productDetail">
    <!-- Product Header -->
    <div class="product-header">
        <!-- Gallery Section -->
        <div class="gallery-section slide-in-left">
            <div class="main-gallery">
                <div class="main-image-container" onclick="openZoom()">
                    @foreach($item->prices as $price)
                        @foreach($price->files as $file)
                            @php
                                /* lấy ảnh Small */
                                $imageMini  = \App\Helpers\Image::getUrlImageMiniByUrlImage($file->file_path);
                                $imageSource = \App\Helpers\Image::getUrlImageCloud($file->file_path);
                                // tiêu đề 
                                $titleProduct = $itemSeo->title ?? $item->seo->title ?? '';
                            @endphp
                            <img id="mainImage" class="main-image lazyload" src="{{ $imageMini }}" data-src="{{ $imageSource }}" alt="{{ $titleProduct }}" title="{{ $titleProduct }}" loading="lazy" />
                            @break;
                        @endforeach
                        @break;
                    @endforeach
                    <div class="zoom-indicator">
                        <svg><use xlink:href="#icon_magnifying-glass-plus"></use></svg> Zoom để xem chi tiết
                    </div>
                </div>
                <button class="gallery-nav prev" onclick="changeImage(-1)">
                    <svg><use xlink:href="#icon_arrow_left"></use></svg>
                </button>
                <button class="gallery-nav next" onclick="changeImage(1)">
                    <svg><use xlink:href="#icon_arrow_right"></use></svg>
                </button>
            </div>
            
            <div class="thumbnail-gallery">
                @php $priceIndex = 0; @endphp
                @php $i = 0; @endphp
                @foreach($item->prices as $price)
                    @php $fileIndex = 0; @endphp
                    @foreach($price->files as $file)
                        @php
                            $imageMini  = \App\Helpers\Image::getUrlImageMiniByUrlImage($file->file_path);
                            $imageSource = \App\Helpers\Image::getUrlImageCloud($file->file_path);
                            $active     = $i==0 ? 'active' : '';
                        @endphp
                        <div class="thumbnail {{ $active }}" 
                            data-price-index="{{ $priceIndex }}" 
                            data-file-index="{{ $fileIndex }}">
                            <img class="lazyload" src="{{ $imageMini }}" data-src="{{ $imageSource }}" alt="" loading="lazy" />
                        </div>
                        @php $fileIndex++; @endphp
                        @php $i++; @endphp
                    @endforeach
                    @php $priceIndex++; @endphp
                @endforeach
            </div>

            <!-- bảng thông tin nhanh - mobile -->
            <div class="show-990">
                @include('main.product.quickInfo')
            </div>
        </div>

        <!-- Product Info -->
        <div class="product-info slide-in-right">
            <div class="product-title">{{ $itemSeo->title ?? '' }}</div>

            <div class="product-subtitle">
                {{-- <div class="product-subtitle_item">
                    <div class="product-sku">Mã sản phẩm: <strong>ZP-CL-001</strong></div>
                </div> --}}
                <div class="product-subtitle_item">
                    <div class="product-rating">
                        <div class="stars">
                            <svg><use xlink:href="#icon_star"></use></svg>
                            <svg><use xlink:href="#icon_star"></use></svg>
                            <svg><use xlink:href="#icon_star"></use></svg>
                            <svg><use xlink:href="#icon_star"></use></svg>
                            <svg><use xlink:href="#icon_star"></use></svg>
                        </div>
                        <span class="rating-text">{{ $item->seo->rating_aggregate_star ?? '--' }}/5 ({{ $item->seo->rating_aggregate_count ?? '--' }} đánh giá)</span>
                    </div>
                </div>
            </div>
            
            <!-- bảng thông tin nhanh - desktop -->
            <div class="hide-990">
                @include('main.product.quickInfo')
            </div>

            <div class="hide-990">
                <div class="options-section">
                    <div class="option-group">
                        <div class="option-label">Tùy chọn:</div>
                        <div class="size-options">
                            @foreach($item->prices as $price)
                                <div class="size-option {{ $loop->first ? 'active' : '' }}" 
                                    data-price-index="{{ $loop->index }}">
                                    {{ $price->code_name ?? '--' }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="responsiveFixed price-box">
                @if(!empty($item->prices)&&$item->prices->isNotEmpty())
                    @foreach($item->prices as $price)
                        @php
                            // giá gạch bỏ
                            $pMax       = !empty($item->price) ? \App\Helpers\Number::getFormatPriceByLanguage($item->price, $language) : '--';
                            // giá bán thức
                            $pOrigin    = !empty($price->price) ? \App\Helpers\Number::getFormatPriceByLanguage($price->price, $language) : '--';
                            // sale off
                            $saleOff    = \App\Helpers\Number::calculatorSaleOffPercent($item->price, $price->price);
                        @endphp
                        <div class="price-section {{ $loop->first ? 'active' : '' }}" 
                            data-price-index="{{ $loop->index }}">
                            <span class="current-price">{{ \App\Helpers\Number::getFormatPriceByLanguage($price->price, $language) }}</span>
                            <span class="original-price">{{ \App\Helpers\Number::getFormatPriceByLanguage($item->price, $language) }}</span>
                            <span class="discount-badge">{{ \App\Helpers\Number::calculatorSaleOffPercent($item->price, $price->price) }}</span>
                        </div>
                    @endforeach
                @endif

                <div class="hide-990">
                    <div class="quantity-selector">
                        <span class="quantity-label">Số lượng:</span>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="quantity-input" value="1" min="1" id="quantityInput">
                            <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-secondary" onclick="setMessageModal('{{ config('data_language_1.'.$language.'.notice_construction_create_post_title') }}', '{{ config('data_language_1.'.$language.'.notice_construction_create_post_body') }}');">
                        <svg><use xlink:href="#icon_cart"></use></svg>
                        Thêm giỏ hàng
                    </button>
                    <button class="btn btn-primary" onclick="setMessageModal('{{ config('data_language_1.'.$language.'.notice_construction_create_post_title') }}', '{{ config('data_language_1.'.$language.'.notice_construction_create_post_body') }}');">
                        <svg><use xlink:href="#icon_money_bill_wave"></use></svg>
                        Mua ngay
                    </button>
                </div>
            </div>

            <div class="hide-990">
                <ul class="features-list">
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/mien-phi-ship-toan-quoc.webp" alt="" title="" loading="lazy" />
                        <div>Miễn phí vận chuyển toàn quốc</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/doi-tra-trong-vong-14-ngay.webp" alt="" title="" loading="lazy" />
                        <div>Đổi trả miễn phí trong 15 ngày</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/bao-hanh-24-thang.webp" alt="" title="" loading="lazy" />
                        <div>Bảo hành 1 đổi 1 trong 24 tháng</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/ho-tro-247.webp" alt="" title="" loading="lazy" />
                        <div>Tư vấn và hỗ trợ 8h00-21h00</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="product-details fade-in">

        @if(!empty($itemSeo->contents)&&$itemSeo->contents->isNotEmpty())
            @foreach($itemSeo->contents as $c)
                {!! $c->content ?? '' !!}
            @endforeach
        @endif

        <!-- Sản phẩm liên quan -->
        <div class="details-section">
            <h2 class="details-title">Sản phẩm liên quan</h2>
            @include('main.category.boxProduct', [
                'idProduct' => $item->id,
            ])
        </div>
        
    </div>
</div>