<div class="footerBox">

    <div class="footerBox_main">
        <div class="container">
            <div class="footerBox_main_info">
                <div class="footerBox_main_info_logo">
                    <img src="https://zenpot.storage.googleapis.com/storage/images/logo-zenpot-full-color.webp" alt="logo {{ strtoupper(config('main_'.env('APP_NAME').'.author_name')) }}" title="logo {{ strtoupper(config('main_'.env('APP_NAME').'.author_name')) }}" loading="lazy" />
                </div>
                <div class="footerBox_main_info_title">
                    {{ strtoupper(config('main_'.env('APP_NAME').'.company_full_name')) }}
                </div>
                <div class="footerBox_main_info_desc">
                    {{ config('main_'.env('APP_NAME').'.company_info') }}
                </div>
                <div class="footerBox_main_info_list">
                    <div class="footerBox_main_info_list_item">
                        <div class="footerBox_main_info_list_item_icon">
                            <svg><use xlink:href="#icon_location_dot"></use></svg>
                        </div>
                        <div class="footerBox_main_info_list_item_text">
                            {{ config('main_'.env('APP_NAME').'.company_address').', '.config('main_'.env('APP_NAME').'.company_province') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="footerBox_main_orther">
                <div class="footerBox_main_orther_contact">
                    <div class="footerBox_main_orther_contact_item">
                        <div class="footerBox_main_orther_contact_item_icon">
                            <svg><use xlink:href="#icon_phone_volume"></use></svg>
                        </div>
                        <div class="footerBox_main_orther_contact_item_text">
                            <div class="footerBox_main_orther_contact_item_text_title">
                                Hotline
                            </div>
                            @php
                                $stringHotline = config('main_'.env('APP_NAME').'.hotline');
                            @endphp
                            <a href="tel:{{ \App\Helpers\Number::normalizePhoneNumber($stringHotline) }}" class="footerBox_main_orther_contact_item_text_desc">
                                {{ $stringHotline }}
                            </a>
                        </div>
                    </div>
                    <div class="footerBox_main_orther_contact_item">
                        <div class="footerBox_main_orther_contact_item_icon">
                            <svg><use xlink:href="#icon_handshake"></use></svg>
                        </div>
                        <div class="footerBox_main_orther_contact_item_text">
                            <div class="footerBox_main_orther_contact_item_text_title">
                                Liên hệ đối tác
                            </div>
                            <a href="mailto:{{ config('main_'.env('APP_NAME').'.email') }}" class="footerBox_main_orther_contact_item_text_desc">
                                {{ config('main_'.env('APP_NAME').'.email') }}
                            </a>
                        </div>
                    </div>
                    <div class="footerBox_main_orther_contact_item">
                        <div class="footerBox_main_orther_contact_item_icon">
                            <svg><use xlink:href="#icon_flag"></use></svg>
                        </div>
                        <div class="footerBox_main_orther_contact_item_text">
                            <div class="footerBox_main_orther_contact_item_text_title">
                                Báo cáo & Khiếu nại
                            </div>
                            <a href="mailto:{{ config('main_'.env('APP_NAME').'.email_report') }}" class="footerBox_main_orther_contact_item_text_desc">
                                {{ config('main_'.env('APP_NAME').'.email_report') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="footerBox_main_orther_menuList">
                    <div class="footerBox_main_orther_menuList_column">
                        <div class="footerBox_main_orther_menuList_column_title">
                            Truy cập nhanh 
                        </div>
                        <div class="footerBox_main_orther_menuList_column_list">
                            <a href="/san-pham-zenpot" title="Sản phẩm của Zenpot" class="footerBox_main_orther_menuList_column_list_item">
                                Sản phẩm
                            </a>
                            <a href="/tin-tuc" title="Tin tức của Zenpot" class="footerBox_main_orther_menuList_column_list_item">
                                Tin tức
                            </a>
                            <a href="/lien-he" title="Liên hệ Zenpot" class="footerBox_main_orther_menuList_column_list_item">
                                Lien hệ
                            </a>
                        </div>
                    </div>
                    <div class="footerBox_main_orther_menuList_column">
                        <div class="footerBox_main_orther_menuList_column_title">
                            Chính sách & Điều khoản
                        </div>
                        <div class="footerBox_main_orther_menuList_column_list">
                            {{-- @foreach($exchangeParent->childs as $itemMenu)

                                @if(!empty($itemMenu->seos)&&$itemMenu->seos->isNotEmpty()&&$itemMenu->flag_show==true)
                                    @foreach($itemMenu->seos as $seo)
                                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                                            @php
                                                $title          = $seo->infoSeo->title ?? null;
                                                $url            = '/'.$seo->infoSeo->slug_full;
                                            @endphp
                                            <a href="/{{ $seo->infoSeo->slug_full }}" title="{{ $title }}" class="footerBox_main_orther_menuList_column_list_item">
                                                {{ $title }}
                                            </a>
                                            @break
                                        @endif
                                    @endforeach
                                @endif

                            @endforeach --}}
                        </div>
                    </div>
                    <div class="footerBox_main_orther_menuList_column">
                        <div class="registryEmailBox">
                            <div class="registryEmailBox_title">
                                Đăng ký nhận thông tin mới nhất từ chúng tôi
                            </div>
                            <div class="registryEmailBox_input">
                                <form id="registryEmail" method="GET" onsubmit="submitFormRegistryEmail('registryEmail'); return false;">
                                    <input 
                                        type="email" 
                                        name="registry_email" 
                                        placeholder="Email của bạn...." 
                                        required 
                                    />
                                    <button type="submit" style="display: none;"></button>
                                </form>
                            </div>
                            <div class="registryEmailBox_button button" onclick="submitFormRegistryEmail('registryEmail')">
                                Đăng ký
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
<div class="copyright">
    <div class="container">{!! config('data_language_1.'.$language.'.copyright') !!}</div>
</div>
@push('scriptCustom')
    <script type="text/javascript">

        /* tính năng registry email ở footer */
        function submitFormRegistryEmail(idForm) {
            event.preventDefault();
            const language  = $('#language').val();
            const inputEmail = $('#' + idForm).find('[name*=registry_email]');
            const valueEmail = inputEmail.val();
            if (isValidEmail(valueEmail)) {
                fetch("/registryEmail?registry_email=" + encodeURIComponent(valueEmail) + "&language=" + encodeURIComponent(language), {
                    method: 'GET',
                    mode: 'cors'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(response => {
                    inputEmail.val('');
                    /* bật thông báo */
                    setMessageModal(response.title, response.content);
                })
                .catch(error => {
                    console.error("Fetch request failed:", error);
                });
            } else {
                inputEmail.val('');
                inputEmail.attr('placeholder', 'Email không hợp lệ!');
            }
        }
    </script>
@endpush