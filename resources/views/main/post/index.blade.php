@extends('layouts.main')
@push('cssFirstView')
    <!-- trường hợp là local thì dùng vite để chạy npm run dev lúc code -->
    @if(env('APP_ENV')=='local')
        @vite('resources/sources/main/post-first-view.scss')
    @else
        @php
            $manifest           = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFirstView       = $manifest['resources/sources/main/post-first-view.scss']['file'];
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

    <!-- STRAT:: Organization Schema -->
    @include('main.schema.organization')
    <!-- END:: Organization Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.article', compact('item'))
    <!-- END:: Article Schema -->

    <!-- STRAT:: Article Schema -->
    @include('main.schema.creativeworkseries', compact('item'))
    <!-- END:: Article Schema -->
    
    {{-- <!-- STRAT:: FAQ Schema -->
    @include('main.schema.itemlist', ['data' => $categories])
    <!-- END:: FAQ Schema --> --}}

    <!-- STRAT:: Title - Description - Social -->
    @include('main.schema.social', ['item' => $item, 'lowPrice' => 1, 'highPrice' => 5])
    <!-- END:: Title - Description - Social -->

    <!-- STRAT:: FAQ Schema -->
    @include('main.schema.faq', ['data' => $itemSeo])
    <!-- END:: FAQ Schema -->
<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.snippets.breadcrumb', ['marginLeftForLogo' => true])

    <div class="pageContent">
        <!-- phần chính bài đăng -->
        <div class="layoutPageCategoryBlog container">
            <div class="layoutPageCategoryBlog_main">
                <!-- tiêu đề h1 -->
                <h1 class="titlePage">{{ $itemSeo->title ?? null }}</h1>
                <!-- headline -->
                <div class="headLineTitle">
                    @if(!empty($item->exchangeOutstandings)&&$item->exchangeOutstandings->isNotEmpty())
                        @foreach($item->exchangeOutstandings as $exchangeOutstanding)
                            @foreach($exchangeOutstanding->infoExchangeTag->seos as $s)
                                @if(!empty($s->infoSeo->language)&&$s->infoSeo->language==$language)
                                    @php
                                        if($loop->index==3) break;
                                    @endphp
                                    <div class="headLineTitle_item">
                                        <svg><use xlink:href="#{{ $exchangeOutstanding->infoExchangeTag->icon ?? null }}"></use></svg>
                                        <div>{{ $s->infoSeo->title ?? null }}</div>
                                    </div>
                                    @break 
                                @endif 
                            @endforeach
                        @endforeach
                    @endif
                    <div class="headLineTitle_item">
                        <svg><use xlink:href="#icon_clock"></use></svg>
                        <div>{{ \App\Helpers\Number::timeAgoVi($item->updated_at) }}</div>
                    </div>
                    {{-- <div class="headLineTitle_item">
                        <svg><use xlink:href="#icon_eye_bold"></use></svg>
                        <div>{{ \App\Helpers\Number::formatViews($item->viewed) }}</div>
                    </div> --}}
                </div>
                <!-- bussiness box -->
                <div class="contentBox">
                    <div class="businessPlanDetailBox">
                        <div class="businessPlanDetailBox_gallery">
                            <!-- gallery box -->
                            @include('main.post.gallery')
                        </div>
                        <div class="businessPlanDetailBox_contentBox">
                            @php
                                $contents = [];
                                foreach($item->seos as $seo){
                                    if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language){
                                        $contents = $seo->infoSeo->postContents;
                                        break;
                                    }
                                }
                            @endphp
                            @include('main.post.content', [
                                'contents'  => $contents,
                            ])

                            <!-- tài liệu đính kèm -->
                            @if(!empty($itemSeo->attachments)&&$itemSeo->attachments->isNotEmpty())
                                <div id="tai-lieu-dinh-kem" class="businessPlanDetailBox_contentBox_item">
                                    <div class="businessPlanDetailBox_contentBox_item_title">
                                        <svg><use xlink:href="#icon_paperclip"></use></svg>
                                        <h2>Tài liệu đính kèm</h2>
                                    </div>
                                    <div class="businessPlanDetailBox_contentBox_item_content">
                                        <div class="attachmentBox">
                                            @foreach($itemSeo->attachments as $attachment)
                                                <a href="{{ \App\Helpers\Image::getUrlImageCloud($attachment->file_cloud) }}" target="_blank" class="attachmentBox_item">
                                                    <div class="attachmentBox_item_title maxLine_2">
                                                        {{ $attachment->title }}
                                                    </div>
                                                    <div class="attachmentBox_item_extension">
                                                        {{ $attachment->file_extension }}
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="layoutPageCategoryBlog_sidebar hide-990">
                @include('main.post.sideBar', [
                    'contents'  => $contents,
                    'contact'   => $item->contact,
                ])
            </div>
        </div>
        <!-- bài đăng liên quan -->
        <div class="sectionBox">
            <div class="container">
                <div class="sectionBox_title">
                    <h2>Tin đăng liên quan</h2>
                </div>
                <!-- box danh sách dự án -->
                <div class="sectionBox_box">
                    @include('main.exchange.bussinessBox', ['post' => [], 'typeLayout' => 'grid'])
                </div>
            </div>
        </div>
    </div>
    
@endsection
@push('modal')
    {{-- <!-- Message Add to Cart -->
    <div id="js_addToCart_idWrite">
        @include('main.cart.cartMessage', [
            'title'     => '',
            'option'    => null,
            'quantity'  => 0,
            'price'     => 0,
            'image'     => null,
            'language'  => $language
        ])
    </div> --}}
@endpush
@push('bottom')
    <!-- box thông tin liên hệ mobile -->
    @php
        $contactName = $item->contact->name ?? null;
        $imageAvatar = config('image.icon_default');
        if(!empty($item->contact->avatar_file_cloud)) {
            $imageAvatar = \App\Helpers\Image::getUrlImageCloud($item->contact->avatar_file_cloud);
        }   
    @endphp
    <div class="founderBoxMobile">
        <div class="container">
            <div class="founderBoxMobile_item avatar">
                <div class="founderBoxMobile_item_image" style="background:url('{{ $imageAvatar }}') no-repeat;background-size:100%;"></div>
            </div>
            @if(!empty($item->contact->zalo))
                <a href="https://zalo.me/{{ $item->contact->zalo }}" target="_blank" class="founderBoxMobile_item zalo">
                    <div class="founderBoxMobile_item_image"></div>
                    <div>Zalo</div>
                </a>
            @endif
            <a href="tel:{{ $item->contact->phone ?? '---' }}" class="founderBoxMobile_item phoneNumber">
                <svg class="founderBoxMobile_item_image"><use xlink:href="#icon_phone_volume"></use></svg>
                <div>{{ $item->contact->phone ?? '---' }}</div>
            </a>
        </div>
    </div>
    <!-- === START:: Zalo Ring === -->
    {{-- @include('main.snippets.zaloRing') --}}
    <!-- === END:: Zalo Ring === -->
@endpush
@push('scriptCustom')
    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function() {
            let interval; // Biến lưu trữ interval cho carousel

            // Sử dụng $(document).on để xử lý sự kiện trên các phần tử động
            $(document).on('mouseenter', '.categoryGrid_box_item_image', function() {
                var $children = $(this).children('img');
                let currentIndex = 0;

                // Đổi ảnh ngay lập tức khi hover lần đầu
                $children.eq(currentIndex).removeClass('active');
                currentIndex = (currentIndex + 1) % $children.length;
                $children.eq(currentIndex).addClass('active');

                // Khởi động carousel để tiếp tục đổi ảnh mỗi 2s
                interval = setInterval(function() {
                    $children.eq(currentIndex).removeClass('active');
                    currentIndex = (currentIndex + 1) % $children.length; // Quay lại từ đầu khi hết phần tử
                    $children.eq(currentIndex).addClass('active');
                }, 2000); // Thay đổi thời gian giữa các vòng lặp nếu cần

            }).on('mouseleave', '.categoryGrid_box_item_image', function() {
                clearInterval(interval); // Dừng carousel khi chuột ra ngoài
                var $children = $(this).children('img');
                $children.removeClass('active'); // Ẩn tất cả hình ảnh khi không hover
                $children.eq(0).addClass('active'); // Hiển thị hình ảnh đầu tiên khi không hover
            });

            // Khởi động carousel với hình ảnh đầu tiên khi trang tải hoặc khi có phần tử mới được thêm vào
            function initializeImages() {
                $('.categoryGrid_box_item_image').each(function() {
                    var $children = $(this).children('img');
                    $children.eq(0).addClass('active'); // Hiển thị hình ảnh đầu tiên
                });
            }

            // Khởi động cho các phần tử ban đầu
            initializeImages();

            // Đếm view
            updateCountViews();

            // loading post 
            loadPostForPage();
        });

        function hideShowContent(elemtBtn){
            const elemtContent      = $(elemtBtn).next();
            const displayContent    = elemtContent.css('display');
            if(displayContent=='none'){
                elemtContent.css('display', 'block');
            }else {
                elemtContent.css('display', 'none');
            }
        }

    </script>
@endpush
