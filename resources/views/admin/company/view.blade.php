@extends('layouts.admin')
@section('content')
    @php
        $titlePage      = 'Thêm Sàn mới';
        $submit         = 'admin.company.createAndUpdate';
        if(!empty($type)&&$type=='edit'){
            $titlePage  = 'Chỉnh sửa Sàn';
        }
    @endphp
    <!-- Start: backgroun để chặn thao tác khi đang dịch content ngầm -->
    @include('admin.category.lock')
    <!-- End: backgroun để chặn thao tác khi đang dịch content ngầm -->
    <form id="formAction" class="needs-validation invalid" action="{{ route($submit) }}" method="POST" novalidate enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="seo_id" name="seo_id" value="{{ $itemSeo->id ?? 0 }}" />
    <input type="hidden" id="seo_id_vi" name="seo_id_vi" value="{{ !empty($item->seo->id)&&$type!='copy' ? $item->seo->id : 0 }}" />
    <input type="hidden" id="company_info_id" name="company_info_id" value="{{ !empty($item->id)&&$type!='copy' ? $item->id : 0 }}" />
    <input type="hidden" id="language" name="language" value="{{ $language ?? 'vi' }}" />
    <input type="hidden" id="type" name="type" value="{{ $type }}" />
        <div class="pageAdminWithRightSidebar withRightSidebar">
            <div class="pageAdminWithRightSidebar_header" style="z-index:1000;position:relative;">
                <div style="width:100%;margin-bottom:10px;">{{ $titlePage }}</div>
                @include('admin.template.languageBox', [
                    'item' => $item,
                    'language' => $language,
                    'routeName' => 'admin.company.view',
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
                <div class="pageAdminWithRightSidebar_main_content">
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Thông tin trang</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.company.formPage', [
                                    'item'              => !empty($itemSourceToCopy) ? $itemSourceToCopy : $item,
                                    'itemSeo'           => !empty($itemSeoSourceToCopy) ? $itemSeoSourceToCopy : $itemSeo,
                                    'flagCopySource'    => !empty($itemSeoSourceToCopy) ? true : false,
                                ])

                            </div>
                        </div>
                    </div>
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
                    <!-- Thông tin thuế -->
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Thông tin thuế</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.company.formTax', [
                                    'item'      => $item,
                                    'itemSeo'   => $itemSeo,
                                ])
                                
                            </div>
                        </div>
                    </div>
                    <!-- Bổ sung VIP -->
                    <div class="pageAdminWithRightSidebar_main_content_item">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">Bổ sung VIP</h4>
                            </div>
                            <div class="card-body">

                                @include('admin.company.formVIP', [
                                    'item'      => $item,
                                    'itemSeo'   => $itemSeo,
                                ])
                                
                            </div>
                        </div>
                    </div>

                    <!-- Dịch vụ -->
                    <div class="pageAdminWithRightSidebar_main_content_item width100 repeater">
                        <div class="card" data-repeater-list="repeater_company_service">
                            <div class="card-header border-bottom">
                                <h4 class="card-title">
                                    Sản phẩm và Dịch vụ
                                    <i class="fa-regular fa-circle-plus" data-repeater-create></i>
                                </h4>
                            </div>
                            @php
                                // Lấy dữ liệu từ old input hoặc fallback về $item->services
                                $dataService = old('repeater_company_service', $item->services);

                                // Kiểm tra và xử lý các trường hợp
                                if ($dataService instanceof \Illuminate\Support\Collection) {
                                    $dataService = $dataService->isNotEmpty() ? $dataService : [null];
                                } elseif (is_array($dataService)) {
                                    $dataService = !empty($dataService) ? $dataService : [null];
                                } else {
                                    $dataService = [null]; // Trường hợp null hoặc không xác định
                                }
                            @endphp
                            @foreach($dataService as $service)
                                <div class="card-body" data-repeater-item>
                                    
                                    @include('admin.company.formService', [
                                        'service'      => $service,
                                    ])

                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <!-- END:: Main content -->

                <!-- START:: Sidebar content -->
                <div class="pageAdminWithRightSidebar_main_rightSidebar">
                    <!-- action -->
                    @include('admin.form.buttonAction', [
                        'routeBack' => 'admin.company.list',
                    ])
                    <!-- action support -->
                    <div class="customScrollBar-y">
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
        $('.repeater').repeater({
            initEmpty: false, // Nếu muốn danh sách trống khi khởi tạo, đặt thành true
            show: function () {
                $(this).slideDown(); // Hiệu ứng khi thêm mới
            },
            hide: function (deleteElement) {
                // if (confirm('Bạn có chắc chắn muốn xóa?')) {
                    $(this).slideUp(deleteElement); // Hiệu ứng khi xóa
                // }
            }
        });
    </script>
@endpush