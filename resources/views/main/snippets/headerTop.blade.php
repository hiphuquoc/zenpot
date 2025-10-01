<div class="headerTop">
   <div class="container">
      <div class="headerTop_item">
         <div class="headerTop_item_hotline">
            @php
               $stringHotline = config('main_'.env('APP_NAME').'.hotline');
            @endphp
            <a href="tel:{{ \App\Helpers\Number::normalizePhoneNumber($stringHotline) }}" title="hotline {{ config('main_'.env('APP_NAME').'.company_name') }}" class="maxLine_1">
               {{ $stringHotline }}
            </a>
         </div>
      </div>
      <div class="headerTop_item">
         <div class="headerTop_item_list">
            <div class="headerTop_item_list_item" onclick="setMessageModal('{{ config('data_language_1.'.$language.'.notice_construction_report_title') }}', '{{ config('data_language_1.'.$language.'.notice_construction_report_body') }}');" style="cursor:pointer;">
               <svg><use xlink:href="#icon_triangle_exclamation"></use></svg>
               <div class="maxLine_1">Báo cáo</div>
            </div>
            <div id="js_checkLoginAndSetShow_button" class="headerTop_item_list_item js_toggleModalLogin"><div class="loginBox" onclick="toggleModalCustomerLoginForm('modalLoginFormCustomerBox');">
               <svg><use xlink:href="#icon_sign_in_alt"></use></svg>
               <div class="maxLine_1">Đăng nhập</div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>