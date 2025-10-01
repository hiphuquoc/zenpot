<!DOCTYPE html>
<html lang="{{ $language ?? 'vi' }}" dir="{{ config('language.'.$language.'.dir') }}">   
    
{{-- class="{{ request()->cookie('view_mode') ?? config('main_'.env('APP_NAME').'.view_mode')[0]['key'] }}" --}}

<!-- === START:: Head === -->
<head>
    @include('main.snippets.head')
</head>
<!-- === END:: Head === -->

<!-- === START:: Body === -->
<body class="background">

    <!-- SVG icon inline -->
    @include('main.snippets.svgSprite')
    
    <!-- === START:: Header === -->
    <input type="hidden" id="language" name="language" value="{{ $language ?? 'vi' }}" />
    <!-- header top -->
    {{-- @include('main.snippets.headerTop') --}}
    <!-- header main ===== xử lý trong service (cache HTML) -->
    {!! $menuHtml ?? null !!}
    <!-- === END:: Header === -->

    <!-- === START:: Content === -->
    <div class="app-content content">
        <div class="content-overlay"></div>
        @yield('content')
    </div>
    <!-- === END:: Content === -->
    
    <!-- === START:: Footer === -->
    @include('main.snippets.footer')
    <!-- === END:: Footer === -->

    <!-- === START:: bottom === -->
    <div class="bottom">
        <div id="smoothScrollToTop" class="gotoTop" onclick="javascript:smoothScrollToTop();">
            <svg><use xlink:href="#icon_arrow_up"></use></svg>
        </div>
        @stack('bottom')
    </div>
    <!-- === END:: bottom === -->

    {{-- <!-- Full loading -->
    <div id="js_toggleFullLoading" class="fullLoading">
        <div class="fullLoading_box">
            <div class="loadingIcon"></div>
            <div id="js_toggleFullLoading_text" class="fullLoading_box_text">{{ config('data_language_3.'.$language.'.the_system_is_processing_your_request') }}</div>
        </div>
    </div> --}}
    
    <!-- ===== START:: Modal -->
    @stack('modal')
    <!-- login form modal -->
    <div id="js_checkLoginAndSetShow_modal">
        <!-- tải ajaax checkLoginAndSetShow() -->
    </div>
    <!-- modal hiển thị thông báo -->
    @include('main.modal.messageModal')
    <!-- ===== END:: Modal -->

    <!-- === START:: Scripts Default === -->
    @include('main.snippets.scriptDefault')
    <!-- === END:: Scripts Default === -->

    <!-- === START:: Scripts Custom === -->
    @stack('scriptCustom')
    <!-- === END:: Scripts Custom === -->
    
</body>
<!-- === END:: Body === -->

</html>