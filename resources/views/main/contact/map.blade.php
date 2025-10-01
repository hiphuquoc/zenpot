<section class="sectionBox">

  <div class="designBox reverse990">

    <div class="designBox_item">
      <iframe class="designBox_item_map" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d2777.902004737128!2d105.0382528304331!3d10.054214535756632!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1svi!2s!4v1758166323308!5m2!1svi!2s" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>  
    </div>
      
    <div class="designBox_item">
      <div class="designBox_item_content">
        <div class="designBox_item_content_titleLarge">
          Liên hệ Zenpot
        </div>
        <h2 class="designBox_item_content_title">
          <div class="designBox_item_content_title_bold">Cách tìm đến Zenpot</div>
          <div>kết nối ngay nhé!</div>
        </h2>
      </div>

      <div class="contactBox">
        <div class="contactBox_item">
          <div class="contactBox_item_icon">
            <svg class="mini"><use xlink:href="#icon_location_dot"></use></svg>
          </div>
          <div class="contactBox_item_content">
            <div class="contactBox_item_content_title">
              {{ config('main_'.env('APP_NAME').'.company_address') }}
            </div>
            <div class="contactBox_item_content_subTitle">
              {{ config('main_'.env('APP_NAME').'.company_province') }}
            </div>
          </div>
        </div>
        <div class="contactBox_item">
          <div class="contactBox_item_icon">
            <svg><use xlink:href="#icon_envelope"></use></svg>
          </div>
          <div class="contactBox_item_content">
            <div class="contactBox_item_content_title">
              {{ config('main_'.env('APP_NAME').'.email') }}
            </div>
            <div class="contactBox_item_content_subTitle">
              {{ config('main_'.env('APP_NAME').'.email_report') }}
            </div>
          </div>
        </div>
        <div class="contactBox_item">
          <div class="contactBox_item_icon">
            <svg class="mini"><use xlink:href="#icon_phone_volume"></use></svg>
          </div>
          <div class="contactBox_item_content">
            <div class="contactBox_item_content_title">
              {{ config('main_'.env('APP_NAME').'.hotline') }}
            </div>
            <div class="contactBox_item_content_subTitle">
              8h00 sáng - 21h00 tối
            </div>
          </div>
        </div>
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