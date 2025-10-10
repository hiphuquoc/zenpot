@extends('layouts.main')
@push('cssFirstView')
    <!-- trường hợp là local thì dùng vite để chạy npm run dev lúc code -->
    @if(env('APP_ENV')=='local')
        @vite('resources/sources/main/category-first-view.scss')
    @else
        @php
            $manifest           = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFirstView       = $manifest['resources/sources/main/category-first-view.scss']['file'];
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
    {{-- @include('main.schema.itemlist', ['data' => $blogs]) --}}
    <!-- END:: FAQ Schema -->

    <!-- STRAT:: FAQ Schema -->
    @include('main.schema.faq', ['data' => $itemSeo])
    <!-- END:: FAQ Schema -->
<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.categoryBlog.banner')
    
    <!-- thân trang -->
    <div class="pageContent container">

        <div class="mainContent">
            
            <div class="categoryBox">

                @include('main.category.sort', [
                    'language'          => $language ?? 'vi',
                    'total'             => $total,
                ])

                <input type="hidden" id="total" name="total" value="{{ $total }}" />
                <input type="hidden" id="loaded" name="loaded" value="{{ $loaded ?? 0 }}" />
                <input type="hidden" id="idNot" name="idNot" value="{{ $idNot ?? 0 }}" />
                <input type="hidden" id="arrayIdCategory" name="arrayIdCategory" value="{{ json_encode($arrayIdCategory) }}" />

                <!-- Products Grid -->
                <div class="products-grid">
                    @if($total>0)
                        @foreach($wallpapers as $wallpaper)
                            @include('main.category.itemProduct', [
                                'wallpaper' => $wallpaper,
                                'language'  => $language,
                                'user'      => $user ?? null
                            ])
                        @endforeach
                    @else 
                        <div>{{ config('data_language_1.'.$language.'.no_suitable_results_found') }}</div>
                    @endif
                </div>

                {{-- <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn" disabled>
                        <svg><use xlink:href="#icon_arrow_left"></use></svg>
                    </button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn">3</button>
                    <button class="pagination-btn">...</button>
                    <button class="pagination-btn">8</button>
                    <button class="pagination-btn">
                        <svg><use xlink:href="#icon_arrow_right"></use></svg>
                    </button>
                </div> --}}
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