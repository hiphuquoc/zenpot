@extends('layouts.main')
@push('cssFirstView')
    <!-- trường hợp là local thì dùng vite để chạy npm run dev lúc code -->
    @if(env('APP_ENV')=='local')
        @vite('resources/sources/main/page-first-view.scss')
    @else
        @php
            $manifest           = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFirstView       = $manifest['resources/sources/main/page-first-view.scss']['file'];
        @endphp
        <style type="text/css">
            {!! file_get_contents(asset('build/' . $cssFirstView)) !!}
        </style>
    @endif
@endpush
@push('headCustom')
<!-- ===== START:: SCHEMA ===== -->
    <!-- STRAT:: Title - Description - Social -->
    @include('main.schema.breadcrumb', compact('breadcrumb'))
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: Title - Description - Social -->
    @php
        $highPrice          = 0;
        $lowPrice           = 0;
    @endphp
    @include('main.schema.social', ['item' => $item, 'lowPrice' => $lowPrice, 'highPrice' => $highPrice])
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: Organization Schema -->
    @include('main.schema.organization')
    <!-- END:: Organization Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.article', compact('item'))
    <!-- END:: Article Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.creativeworkseries', compact('item'))
    <!-- END:: Article Schema -->

    <!-- STRAT:: FAQ Schema -->
    @include('main.schema.faq', ['data' => $itemSeo])
    <!-- END:: FAQ Schema -->
<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.categoryBlog.banner')

    <div class="pageContent">
        <div class="layoutPageCategoryBlog container">
            <div class="layoutPageCategoryBlog_main">
                
                <!-- khoảng cách -->
                <div class="spaceHeadWithBanner"></div>

                @if(!empty(trim($dataContent['toc_content'])))
                    <div id="tocContentMain">{!! $dataContent['toc_content'] !!}</div>
                @endif
                    
                <div class="distanceBetweenBox">
                    <!-- Nội dung -->
                    <div id="js_buildTocContentMain_element" class="contentBox">
                        @php
                            $contentShow = !empty(trim($dataContent['content'])) ? $dataContent['content'] : '<div>Nội dung đang được cập nhật!</div>';
                            $htmlContent = \Illuminate\Support\Facades\Blade::render($contentShow);
                        @endphp
                        {!! $htmlContent !!}
                    </div>

                    <!-- faqs -->
                    @include('main.page.faq', compact('itemSeo'))
                    <!-- liên hệ -->
                    @include('main.page.contact')
                </div>

            </div>
            <div class="layoutPageCategoryBlog_sidebar">
                @include('main.page.sideBar')
            </div>
        </div>
    </div>

@endsection
@push('modal')

@endpush
@push('bottom')
    <!-- Header bottom -->
    {{-- @include('main.snippets.headerBottom') --}}
    <!-- === START:: Zalo Ring === -->
    {{-- @include('main.snippets.zaloRing') --}}
    <!-- === END:: Zalo Ring === -->
@endpush
@push('scriptCustom')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            /* build tocContent khi scroll gần tới */
            const elementBuildTocContent = $('#js_buildTocContentMain_element');
            /* build toc content */
            if(elementBuildTocContent.length){
                if (!elementBuildTocContent.hasClass('loaded')) {
                    buildTocContentMain('js_buildTocContentMain_element');
                }
            }    
        });
    </script>
@endpush