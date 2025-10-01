<div class="productDetail">
    <!-- Product Header -->
    <div class="product-header">
        <!-- Gallery Section -->
        <div class="gallery-section slide-in-left">
            <div class="main-gallery">
                <div class="main-image-container" onclick="openZoom()">
                    <img id="mainImage" class="main-image" src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=500&fit=crop" alt="Chậu gỗ ZenPot">
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
                <div class="thumbnail active" onclick="setMainImage(0)">
                    <img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411" alt="Thumbnail 1">
                </div>
                <div class="thumbnail" onclick="setMainImage(1)">
                    <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b" alt="Thumbnail 2">
                </div>
                <div class="thumbnail" onclick="setMainImage(2)">
                    <img src="https://images.unsplash.com/photo-1606041008023-472dfb5e530f" alt="Thumbnail 3">
                </div>
                <div class="thumbnail" onclick="setMainImage(3)">
                    <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96" alt="Thumbnail 4">
                </div>
                <div class="thumbnail" onclick="setMainImage(4)">
                    <img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07" alt="Thumbnail 5">
                </div>
                <div class="thumbnail" onclick="setMainImage(5)">
                    <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b" alt="Thumbnail 2">
                </div>
                <div class="thumbnail" onclick="setMainImage(6)">
                    <img src="https://images.unsplash.com/photo-1606041008023-472dfb5e530f" alt="Thumbnail 3">
                </div>
                <div class="thumbnail" onclick="setMainImage(7)">
                    <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96" alt="Thumbnail 4">
                </div>
                <div class="thumbnail" onclick="setMainImage(8)">
                    <img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07" alt="Thumbnail 5">
                </div>
            </div>

            <!-- bảng thông tin nhanh - mobile -->
            <div class="show-990">
                <div class="quick-info">
                    <div class="quick-info-row">
                        <div class="quick-info-label">Mã sản phẩm</div>
                        <div class="quick-info-value">ZP-001</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Kích thước (phủ bì)</div>
                        <div class="quick-info-value">15cm x 15cm x 12cm (DxRxC)</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Dung tích chỗ trồng</div>
                        <div class="quick-info-value">0.8 L</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Khối lượng</div>
                        <div class="quick-info-value">0.9 kg</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Chất liệu</div>
                        <div class="quick-info-value">Xi măng kèm phụ gia cao cấp, sơn 4 lớp chịu nhiệt, kháng UV, chống trầy</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Ứng dụng</div>
                        <div class="quick-info-value">Chậu bonsai mini, decor để bàn</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Tình trạng</div>
                        <div class="quick-info-value in-stock">Còn hàng</div>
                    </div>
                </div>
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
                        <span class="rating-text">4.8/5 (124 đánh giá)</span>
                    </div>
                </div>
            </div>
            
            <!-- bảng thông tin nhanh - desktop -->
            <div class="hide-990">
                <div class="quick-info">
                    <div class="quick-info-row">
                        <div class="quick-info-label">Mã sản phẩm</div>
                        <div class="quick-info-value">ZP-001</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Kích thước (phủ bì)</div>
                        <div class="quick-info-value">15cm x 15cm x 12cm (DxRxC)</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Dung tích chỗ trồng</div>
                        <div class="quick-info-value">0.8 L</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Khối lượng</div>
                        <div class="quick-info-value">0.9 kg</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Chất liệu</div>
                        <div class="quick-info-value">Xi măng kèm phụ gia cao cấp, sơn 4 lớp chịu nhiệt, kháng UV, chống trầy</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Ứng dụng</div>
                        <div class="quick-info-value">Chậu bonsai mini, decor để bàn</div>
                    </div>
                    <div class="quick-info-row">
                        <div class="quick-info-label">Tình trạng</div>
                        <div class="quick-info-value in-stock">Còn hàng</div>
                    </div>
                </div>
            </div>

            <div class="hide-990">
                <div class="options-section">
                    <div class="option-group">
                        <div class="option-label">Tùy chọn:</div>
                        <div class="size-options">
                            <div class="size-option active">Màu sáng - có đế lót</div>
                            <div class="size-option">Màu sáng - không đế lót</div>
                            <div class="size-option">Màu tối - có đế lót</div>
                            <div class="size-option">Màu tối - không đế lót</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="responsiveFixed">
                <div class="price-section">
                    <span class="current-price">250,000₫</span>
                    <span class="original-price">600,000₫</span>
                    <span class="discount-badge">-56%</span>
                </div>

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
                    <button class="btn btn-secondary">
                        <svg><use xlink:href="#icon_cart"></use></svg>
                        Thêm giỏ hàng
                    </button>
                    <button class="btn btn-primary">
                        <svg><use xlink:href="#icon_money_bill_wave"></use></svg>
                        Mua ngay
                    </button>
                </div>
            </div>

            <div class="hide-990">
                <ul class="features-list">
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/mien-phi-ship-toan-quoc-large.webp" alt="" title="" />
                        <div>Miễn phí vận chuyển toàn quốc</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/doi-tra-trong-vong-14-ngay-large.webp" alt="" title="" />
                        <div>Đổi trả miễn phí trong 15 ngày</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/bao-hanh-24-thang-large.webp" alt="" title="" />
                        <div>Bảo hành 1 đổi 1 trong 24 tháng</div>
                    </li>
                    <li>
                        <img src="https://zenpot.storage.googleapis.com/storage/images/ho-tro-247-large.webp" alt="" title="" />
                        <div>Tư vấn và hỗ trợ 8h00-21h00</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="product-details fade-in">
        <div class="details-section">
            <h2 class="details-title">Mô Tả Sản Phẩm</h2>
            <div class="description-content">
                <p>{{ $itemSeo->title ?? '' }} được chế tác từ gỗ tự nhiên cao cấp, mang đến vẻ đẹp rustic và gần gũi với thiên nhiên cho không gian sống của bạn. Sản phẩm được thiết kế tỉ mỉ bởi các nghệ nhân có kinh nghiệm, đảm bảo từng chi tiết đều hoàn hảo.</p>
                
                <p>Với thiết kế hiện đại nhưng vẫn giữ được nét truyền thống, chậu gỗ ZenPot không chỉ là nơi trồng cây mà còn là một món đồ trang trí sang trọng, nâng tầm thẩm mỹ cho ngôi nhà của bạn.</p>
                
                <p>Sản phẩm đã được xử lý chống mối mọt, chống thấm và có độ bền cao, phù hợp với khí hậu nhiệt đới của Việt Nam. Đây là lựa chọn hoàn hảo cho những ai yêu thích phong cách sống xanh và bền vững.</p>
            </div>
        </div>

        <div class="details-section">
            <h2 class="details-title">Thông Số Kỹ Thuật</h2>
            <div class="specifications-grid">
                <div class="spec-item">
                    <span class="spec-label">Chất liệu:</span>
                    <span class="spec-value">Gỗ tự nhiên (Gỗ cao su/Gỗ thông)</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Kích thước:</span>
                    <span class="spec-value">15cm x 15cm x 12cm (DxRxC)</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Trọng lượng:</span>
                    <span class="spec-value">0.8 kg</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Màu sắc:</span>
                    <span class="spec-value">Nâu gỗ tự nhiên</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Xuất xứ:</span>
                    <span class="spec-value">Việt Nam</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Bảo hành:</span>
                    <span class="spec-value">12 tháng</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Chất liệu:</span>
                    <span class="spec-value">Gỗ tự nhiên (Gỗ cao su/Gỗ thông)</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Kích thước:</span>
                    <span class="spec-value">15cm x 15cm x 12cm (DxRxC)</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Trọng lượng:</span>
                    <span class="spec-value">0.8 kg</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Màu sắc:</span>
                    <span class="spec-value">Nâu gỗ tự nhiên</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Xuất xứ:</span>
                    <span class="spec-value">Việt Nam</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Bảo hành:</span>
                    <span class="spec-value">12 tháng</span>
                </div>
            </div>
        </div>

        <!-- Sản phẩm liên quan -->
        
         <div class="details-section">
            <h2 class="details-title">Sản phẩm liên quan</h2>
            @include('main.category.product')
        </div>
        
    </div>
</div>

<!-- Zoom Modal -->
<div class="zoom-modal" id="zoomModal" onclick="closeZoom()">
    <span class="zoom-close">&times;</span>
    <img id="zoomImage" src="" alt="Zoomed image">
</div>