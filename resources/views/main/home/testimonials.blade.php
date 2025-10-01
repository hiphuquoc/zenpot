@php
    $testimonials = [
      [
        'content' => 'Mình đặt một chậu Zenpot nhỏ để bàn làm việc, khi nhận hàng rất thích cách đóng gói cẩn thận, không bị sứt mẻ. Đặt cây vào thấy góc làm việc như có thêm năng lượng, nhìn tự nhiên và dịu mắt hơn hẳn.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/1.webp',
      ],
      [
        'content' => 'Chậu Zenpot có thiết kế thoát nước hợp lý nên cây mình trồng rất khỏe. Đặc biệt bề mặt chậu mịn, dễ lau chùi, mỗi lần thay cây cũng không mất công vệ sinh nhiều.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/2.webp',
      ],
      [
        'content' => 'Mình thích nhất cảm giác khi chạm vào chậu Zenpot, mát tay và vân gỗ tự nhiên. Đặt trong phòng khách cạnh bộ sofa gỗ rất hợp, tạo cảm giác không gian hài hòa hơn.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/3.webp',
      ],
      [
        'content' => 'Ban đầu chỉ định mua để thử, nhưng khi đặt bonsai vào mình bất ngờ vì độ hài hòa của màu chậu với cây. Chậu nặng vừa phải nên để ngoài ban công gió to cũng không lo bị đổ.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/4.webp',
      ],
      [
        'content' => 'Mình chọn Zenpot để làm quà tân gia cho bạn. Khi tặng bạn mình cũng khen kiểu dáng độc đáo, nhìn như một tác phẩm thủ công, làm quà rất ý nghĩa mà không sợ đụng hàng.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/5.webp',
      ],
      [
        'content' => 'Điều mình thích nhất ở Zenpot là cảm giác tự nhiên như mang một mảnh thiên nhiên thật vào nhà. Không quá cầu kỳ nhưng có nét tinh tế riêng, hợp cả phong cách hiện đại lẫn cổ điển.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/6.webp',
      ],
      [
        'content' => 'Sau vài tháng đặt ngoài trời, chậu Zenpot vẫn giữ được màu đẹp và không bị ố. Mình trồng sen đá, cây phát triển tốt nhờ hệ thống thoát nước ổn, không bị úng rễ như mấy chậu trước.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/7.webp',
      ],
      [
        'content' => 'Mình tìm một chậu nhỏ gọn để đặt trên kệ sách, Zenpot đúng nhu cầu. Kích thước vừa phải, nhẹ hơn mình nghĩ nhưng vẫn chắc chắn, dễ sắp xếp và di chuyển khi dọn dẹp.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/8.webp',
      ],
      [
        'content' => 'Khác biệt ở Zenpot là sự tinh tế trong từng đường vân, mình nhìn mãi không chán. Khi bạn bè tới chơi ai cũng hỏi mua ở đâu vì nhìn lạ mắt mà hợp nhiều kiểu cây khác nhau.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/9.webp',
      ],
      [
        'content' => 'Đợt này mình mua thêm vài chậu Zenpot để trồng cây gia vị trong bếp. Màu chậu hòa với màu xanh của lá tạo cảm giác rất tươi mát, nấu ăn xong nhìn góc bếp cũng dễ chịu hơn nhiều.',
        'avatar'  => 'https://zenpot.storage.googleapis.com/storage/images/10.webp',
      ],
    ];
@endphp

<section class="sectionBox">
    <div class="designBox">
        <div class="designBox_item">
            <div class="designBox_item_content">
                <div class="designBox_item_content_titleLarge">
                    Cảm nhận của Khách hàng
                </div>
                <h2 class="designBox_item_content_title">
                  <div class="designBox_item_content_title_bold">Vì sao họ yêu Zenpot?</div>
                  <div>và điều họ chia sẻ</div>
                </h2>
                <div class="testimonials">
                  <div class="testimonials-slider swiper">
                    <div class="swiper-wrapper">
                      @foreach($testimonials as $testimonial)
                        <div class="testimonial swiper-slide">
                          <div class="testimonial-content">
                            <div class="testimonial-quote" aria-hidden="true">
                              <svg><use xlink:href="#icon_quote-right"></use></svg>
                            </div>
                            <p>
                              {{ $testimonial['content'] ?? '' }}
                            </p>
                          </div>

                          <div class="testimonial-author">
                            <img src="{{ $testimonial['avatar'] ?? '' }}" alt="Cảm nhận khách hàng {{ $loop->index + 1 }}" loading="lazy" />
                            <span>Khách hàng {{ $loop->index + 1 }}</span>
                          </div>
                        </div>
                      @endforeach
                    </div>

                    <!-- Swiper controls -->
                    <div class="swiper-pagination" aria-hidden="true"></div>
                    <button class="swiper-button-prev" aria-label="Previous"></button>
                    <button class="swiper-button-next" aria-label="Next"></button>
                  </div>
                </div>

            </div>
        </div>

        <div class="designBox_item hide-990">
            <div class="designBox_item_image">
                <img  class="leftImg" src="https://zenpot.storage.googleapis.com/storage/images/chau-cay-thuy-tinh-de-ban-trang-tri-5.webp" alt="" title="" loading="lazy" />
            </div>
            
        </div>
        

    </div>

</section>

@push('scriptCustom')
    <script type="text/javascript">
        const swiper = new Swiper('.testimonials-slider', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 1114000,
                disableOnInteraction: false,
            },
        });
    </script>
@endpush