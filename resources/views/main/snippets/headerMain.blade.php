<!-- START:: Menu Desktop -->
<div id="js_addClassWhenScrolledPast" class="headerMain">
    <div class="container">
        <div class="headerMain_item">
            <ul>
                <!-- Trang chủ -->
                <li>
                    <a href="/" title="{{ config('data_language_1.'.$language.'.home') }}" class="logoMain">
                        <div class="logoMain_logo"></div>
                    </a>
                </li>
            </ul>
        </div>
        <!-- menu list desktop -->
        <div class="headerMain_item hide-990">
            <ul>
                <li>
                    <a href="/" title="Trang chủ Zenpot">
                        <img src="{{ asset('storage/images/svg/home-fff.svg') }}" alt="Trang chủ Zenpot" title="Trang chủ Zenpot" style="margin-top:-6px;" />
                    </a>
                </li>
                <!-- Về chúng tôi -->
                @if(!empty($infoPageAboutUs))      
                    @foreach($infoPageAboutUs->seos as $seo)
                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                            @php
                                $title = $seo->infoSeo->title ?? null;
                            @endphp
                            <li>
                                <a href="/{{ $seo->infoSeo->slug_full }}" title="{{ $title }}">
                                    <div>{{ $title }}</div>
                                </a>
                            </li>
                        @break
                        @endif
                    @endforeach
                @endif
                <!-- Sản phẩm -->
                <li>
                    <div>
                        <a href="/san-pham-zenpot" title="Sản phẩm của Zenpot">
                            <div>
                                Sản phẩm
                            </div>
                        </a>
                    </div>
                </li>
                <!-- Tin tức - Kiến thức -->
                <li>
                    <div>
                        <a href="/tin-tuc" title="Tin tức của Zenpot">
                            <div>
                                Tin tức - Kiến thức
                            </div>
                        </a>
                    </div>
                </li>
                <!-- Liên hệ -->
                <li>
                    <div class="hasChild">
                        <a href="/lien-he" title="Liên hệ Zenpot">
                            <div>
                                Liên hệ
                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
        <!-- nút tạo tin đăng -->
        <div class="headerMain_item">
            <ul>
                <li>
                    @include('main.cart.cartSort')
                </li>

                <!-- menu bar mobile -->
                <li class="show-990">
                    <div class="iconMenuMobile" onclick="openCloseElemt('nav-mobile');">
                        <div>
                            <svg><use xlink:href="#icon_bars"></use></svg>
                        </div>
                    </div>
                </li>

            </ul>
        </div>

    </div>
</div>

<!-- START:: Menu Mobile -->
<div id="nav-mobile">
    <div class="nav-mobile">
        <div class="nav-mobile_bg" onclick="openCloseElemt('nav-mobile');"></div>
        <div class="nav-mobile_main customScrollBar-y">
            <div class="nav-mobile_main__exit" onclick="openCloseElemt('nav-mobile');">
                <svg><use xlink:href="#icon_close"></use></svg>
            </div>
            <a href="/" title="{{ config('data_language_1.'.$language.'.home') }}" class="logoMain">
                <div class="logoMain_logo"></div>
            </a>
            <ul>
                <li>
                    <a href="/" title="{{ config('data_language_1.'.$language.'.home') }}">
                        <svg><use xlink:href="#icon_home"></use></svg>
                        <div class="nav-mobile_main__title">{{ config('data_language_1.'.$language.'.home') }}</div>
                    </a>
                </li>
                <!-- Về chúng tôi -->
                @if(!empty($infoPageAboutUs))      
                    @foreach($infoPageAboutUs->seos as $seo)
                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                            @php
                                $title = $seo->infoSeo->title ?? null;
                            @endphp
                            <li>
                                <a href="/{{ $seo->infoSeo->slug_full }}" title="{{ $title }}">
                                    <svg><use xlink:href="#icon_question"></use></svg>
                                    <div class="nav-mobile_main__title">{{ $title }}</div>
                                </a>
                            </li>
                        @break
                        @endif
                    @endforeach
                @endif
                <!-- Sản phẩm -->
                <li>
                    <a href="/san-pham-zenpot" title="Sản phẩm Zenpot">
                        <svg><use xlink:href="#icon_layer-group"></use></svg>
                        <div class="nav-mobile_main__title">Sản phẩm Zenpot</div>
                    </a>
                </li>
                <!-- Tin tức - Kiến thức -->
                @foreach($categoryBlogParent->seos as $seo)
                    @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language)
                        @php
                            $title  = $seo->infoSeo->title ?? null;
                        @endphp
                        <li>
                            <div onclick="showHideListMenuMobile(this);">
                                <svg><use xlink:href="#icon_blog"></use></svg>
                                <a href="/{{ $seo->infoSeo->slug_full ?? '' }}" class="nav-mobile_main__title" title="{{ $title }}">{{ $title }}</a>
                                <svg><use xlink:href="#icon_plus"></use></svg>
                            </div>
                            <ul>
                                @foreach($categoryBlogParent->childs as $categoryBlog)
                                    @foreach($categoryBlog->seos as $s)
                                        @if(!empty($s->infoSeo->language)&&$s->infoSeo->language==$language)
                                            @php
                                                $title = $s->infoSeo->title ?? null;
                                            @endphp
                                            <li>
                                                <a href="/{{ $s->infoSeo->slug_full ?? '' }}" title="{{ $title }}">
                                                    <div class="nav-mobile_main__title">{{ $title }}</div>
                                                </a>
                                            </li>
                                            @break 
                                        @endif
                                    @endforeach
                                @endforeach
                            </ul>
                        </li>
                        @break
                    @endif
                @endforeach
                <!-- Liên hệ -->
                @if(!empty($infoPageContact))      
                    @foreach($infoPageContact->seos as $seo)
                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                            @php
                                $title = $seo->infoSeo->title ?? null;
                            @endphp
                            <li>
                                <a href="/{{ $seo->infoSeo->slug_full }}" title="{{ $title }}">
                                    <svg><use xlink:href="#icon_map_pin"></use></svg>
                                    <div class="nav-mobile_main__title">{{ $title }}</div>
                                </a>
                            </li>
                        @break
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>