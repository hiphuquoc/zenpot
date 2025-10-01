@extends('layouts.admin')
@section('content')
    @php
        $titlePage      = 'Thêm Bài Viết mới';
        $submit         = 'admin.post.createAndUpdate';
        if(!empty($type)&&$type=='edit'){
            $titlePage  = 'Chỉnh sửa Bài Viết';
        }
    @endphp
    <!-- Start: backgroun để chặn thao tác khi đang dịch content ngầm -->
    @include('admin.category.lock')
    <!-- End: backgroun để chặn thao tác khi đang dịch content ngầm -->
    <form id="formAction" class="needs-validation invalid" action="{{ route($submit) }}" method="POST" novalidate enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="seo_id" name="seo_id" value="{{ $itemSeo->id ?? 0 }}" />
    <input type="hidden" id="seo_id_vi" name="seo_id_vi" value="{{ !empty($item->seo->id)&&$type!='copy' ? $item->seo->id : 0 }}" />
    <input type="hidden" id="post_info_id" name="post_info_id" value="{{ !empty($item->id)&&$type!='copy' ? $item->id : 0 }}" />
    <input type="hidden" id="language" name="language" value="{{ $language ?? 'vi' }}" />
    <input type="hidden" id="type" name="type" value="{{ $type }}" />
        <div class="pageAdminWithRightSidebar withRightSidebar">
            <div class="pageAdminWithRightSidebar_header" style="z-index:1000;position:relative;">
                <div style="width:100%;margin-bottom:10px;">{{ $titlePage }}</div>
                @include('admin.template.languageBox', [
                    'item' => $item,
                    'language' => $language,
                    'routeName' => 'admin.post.view',
                ])
            </div>
            
            <!-- Error -->
            @if ($errors->any())
                <ul class="errorList">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <!-- MESSAGE -->
            @include('admin.template.messageAction')
            
            <div class="pageAdminWithRightSidebar_main">
                <!-- START:: Main content -->
                <div class="pageAdminWithRightSidebar_main_content" data-repeater-list="contents">
                    <!-- Thông tin trang -->
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Thông tin trang</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.post.formPage', [
                                    'item'              => !empty($itemSourceToCopy) ? $itemSourceToCopy : $item,
                                    'itemSeo'           => !empty($itemSeoSourceToCopy) ? $itemSeoSourceToCopy : $itemSeo,
                                    'flagCopySource'    => !empty($itemSeoSourceToCopy) ? true : false,
                                ])

                            </div>
                        </div>
                    </div>
                    <!-- Thông tin SEO -->
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Thông tin SEO</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.form.formSeo', [
                                    'item'              => !empty($itemSourceToCopy) ? $itemSourceToCopy : $item,
                                    'itemSeo'           => !empty($itemSeoSourceToCopy) ? $itemSeoSourceToCopy : $itemSeo,
                                    'flagCopySource'    => !empty($itemSeoSourceToCopy) ? true : false,
                                    'idSeoSource'       => $itemSeoSourceToCopy->id ?? 0
                                ])
                                
                            </div>
                        </div>
                    </div>
                    <!-- Thông tin liên hệ -->
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Thông tin liên hệ</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.form.formContact', [
                                    'item'              => !empty($itemSourceToCopy) ? $itemSourceToCopy : $item,
                                ])
                                
                            </div>
                        </div>
                    </div>
                    <!-- tài liệu đính kèm -->
                    <div class="pageAdminWithRightSidebar_main_content_item" data-repeater-list="contents">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Tài liệu đính kèm</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.post.formAttachment', [
                                    'data'  => $itemSeo->attachments,
                                ])
                                
                            </div>
                        </div>
                    </div>
                    <!-- nội dung -->
                    @php
                        $dataContents   = old('contents') ?? $itemSeo->postContents;
                        $dataContents   = $dataContents->isNotEmpty() ? $dataContents : [null];
                    @endphp
                    @foreach($dataContents as $content)
                        @include('admin.post.formContentPost', compact('content'))
                    @endforeach
                </div>
                <!-- END:: Main content -->

                <!-- START:: Sidebar content -->
                <div class="pageAdminWithRightSidebar_main_rightSidebar">
                    <!-- action -->
                    @include('admin.post.buttonAction', [
                        'routeBack' => 'admin.post.list',
                    ])
                    <!-- action support -->
                    <div class="customScrollBar-y">
                        <div class="pageAdminWithRightSidebar_main_rightSidebar_item">
                            <button class="btn btn-icon btn-primary waves-effect waves-float waves-light" type="button" aria-label="Thêm" style="width:100%;" data-repeater-create>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-25"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Thêm phiên bản Content</span>
                            </button>
                        </div>
                        <!-- Form Upload -->
                        <div class="pageAdminWithRightSidebar_main_rightSidebar_item">
                            @include('admin.form.formImage')
                        </div>
                        <!-- Form Gallery -->
                        <div class="pageAdminWithRightSidebar_main_rightSidebar_item">
                            @include('admin.post.formGallery')
                        </div>
                    </div>
                </div>
                <!-- END:: Sidebar content -->
            </div>
        </div>
    </form>
@endsection
@push('modal')
    <!-- modal chọn thumnail -->
    @include('admin.form.formModalChooseLanguageBeforeDeletePage')
@endpush

@push('scriptCustom')
    <script type="text/javascript">
        $('.pageAdminWithRightSidebar_main').repeater({
            show: function () {
                $(this).slideDown();
                callTiny(); // Gọi hàm sau khi phần tử được thêm vào
            }
        });
    </script>
@endpush