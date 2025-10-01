@extends('layouts.main')
@push('cssFirstView')
    <!-- trường hợp là local thì dùng vite để chạy npm run dev lúc code -->
    @if(env('APP_ENV')=='local')
        @vite('resources/sources/main/category-blog-first-view.scss')
    @else
        @php
            $manifest           = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFirstView       = $manifest['resources/sources/main/category-blog-first-view.scss']['file'];
        @endphp
        <style type="text/css">
            {!! file_get_contents(asset('build/' . $cssFirstView)) !!}
        </style>
    @endif
@endpush
@push('headCustom')
<!-- ===== START:: SCHEMA ===== -->
    <!-- STRAT:: Title - Description - Social -->
    @php
        $highPrice          = 0;
        $lowPrice           = $highPrice;
    @endphp
    @include('main.schema.social', ['item' => $item, 'lowPrice' => $lowPrice, 'highPrice' => $highPrice])
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: Title - Description - Social -->
    @include('main.schema.breadcrumb', compact('breadcrumb'))
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
    @include('main.schema.itemlist', ['data' => $blogs])
    <!-- END:: FAQ Schema -->

    <!-- STRAT:: FAQ Schema -->
    @include('main.schema.faq', ['data' => $itemSeo])
    <!-- END:: FAQ Schema -->
<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.categoryBlog.banner')
    
    <!-- thân trang -->
    <div class="pageContent">
        <div class="layoutPageCategoryBlog container">
            <div class="layoutPageCategoryBlog_main">
                
                <!-- bussiness box -->
                <div class="mainContentBox">
                    
                    <div class="mainContentBox_filter">
                        <!-- filter box -->
                        @include('main.categoryBlog.sort', [
                            'language'          => $language ?? 'vi',
                            'total'             => $item->blogs->count(),
                        ])
                    </div>
                    
                     <!-- bài viết con -->
                    <div class="blogListBox">
                        @if(!empty($blogs)&&$blogs->count()>0)
                            @foreach($blogs as $blog)
                                @foreach($blog->seos as $seo)
                                    @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                                        @php
                                            $title = $seo->infoSeo->title ?? '';
                                            $urlArticle = env('APP_URL').'/'.$seo->infoSeo->slug_full;
                                        @endphp
                                        <div class="blogListBox_item">
                                            <a href="{{ $urlArticle }}" class="blogListBox_item_image" title="{{ $title }}">
                                                @if(!empty($blog->seo->image))
                                                    @php
                                                        $imageMini  = \App\Helpers\Image::getUrlImageMiniByUrlImage($blog->seo->image);
                                                        $imageSmall = \App\Helpers\Image::getUrlImageSmallByUrlImage($blog->seo->image);
                                                        $imageLarge = \App\Helpers\Image::getUrlImageLargeByUrlImage($blog->seo->image);
                                                    @endphp
                                                    <picture>
                                                        <source media="(max-width: 577px)" srcset="{{ $imageSmall }}">
                                                        <img 
                                                            class="lazyload" 
                                                            src="{{ $imageMini }}" 
                                                            data-src="{{ $imageLarge }}" 
                                                            alt="{{ $title }}" 
                                                            title="{{ $title }}" 
                                                            loading="lazy" 
                                                        />
                                                    </picture>
                                                @endif
                                            </a>
                                            <div class="blogListBox_item_content">
                                                <a href="{{ $urlArticle }}" class="blogListBox_item_content_title maxLine_3" title="{{ $title }}">
                                                    <h2>{{ $title }}</h2>
                                                </a>
                                                <div class="blogListBox_item_content_info">
                                                    <div class="blogListBox_item_content_info_item maxLine_1">
                                                        <svg><use xlink:href="#icon_user"></use></svg>
                                                        <div>Admin</div>
                                                    </div> 
                                                    <div class="blogListBox_item_content_info_item maxLine_1">
                                                        <svg><use xlink:href="#icon_clock_bold"></use></svg>
                                                        <div>{!! date('d \t\h\á\n\g m, Y', strtotime($seo->infoSeo->created_at)) !!}</div>
                                                    </div>
                                                    {{-- <div class="blogListBox_item_content_info_item maxLine_1">
                                                        <svg style="transform: scale(1.15)"><use xlink:href="#icon_eye_bold"></use></svg>
                                                        <div>{{ \App\Helpers\Number::formatViews($blog->viewed) }}</div>
                                                    </div> --}}
                                                </div>
                                                <div class="blogListBox_item_content_desc maxLine_4">
                                                    {!! !empty($seo->infoSeo->contents[0]->content) ? strip_tags($seo->infoSeo->contents[0]->content) : '' !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                @endforeach
                            @endforeach
                        @else 
                            <div>{{ config('data_language_1.'.$language.'.no_suitable_results_found') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Pagination -->
                @include('main.snippets.paginate', [
                    'data'  => $blogs,
                ])
                
            </div>
            <!-- sidebar -->
            <div class="layoutPageCategoryBlog_sidebar">
                <!-- bai viết nổi bật -->
                @if(!empty($blogFeatured)&&$blogFeatured->count()>0)
                    <div class="blogSiderbarBox oneColumn">
                        <div class="blogSiderbarBox_head">
                            {{ config('data_language_1.'.$language.'.featured_articles') }}
                        </div>
                        <div class="blogSiderbarBox_box">
                            @include('main.categoryBlog.blogFeatured', [
                                'blogs'     => $blogFeatured, 
                                'language'  => $language,
                            ])
                        </div>
                    </div>
                @endif
                <!-- danh sách category_blog -->
                @if(!empty($categoriesLv2)&&$categoriesLv2->count()>0)
                    @include('main.categoryBlog.categoryBlogList', [
                        'categories'    => $categoriesLv2,
                        'language'      => $language,
                    ])
                @endif
            </div>
        </div>
    </div>

@endsection
@push('modal')
    {{-- <!-- Message Add to Cart -->
    <div id="js_addToCart_idWrite">
        @include('main.cart.cartMessage', [
            'title'     => null,
            'option'    => null,
            'quantity'  => 0,
            'price'     => 0,
            'image'     => null,
            'language'  => $language
        ])
    </div> --}}
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
    
    </script>
@endpush