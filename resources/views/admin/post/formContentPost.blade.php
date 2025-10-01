<div class="pageAdminWithRightSidebar_main_content_item width100" data-repeater-item>
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">
                Phiên bản của nội dung
                <i class="fa-solid fa-circle-xmark" data-repeater-delete></i>
            </h4>
        </div>
        <div class="card-body">
            <input type="hidden" name="contents[0][id]" value="{{ $content->id ?? '' }}">
            <div class="formBox">
                <!-- One Dual Column -->
                <div class="formBox_full flexBox">
                    <div class="flexBox_item">
                        <div class="formBox_full_item">
                            <label class="form-label inputRequired" for="content_title">Title</label>
                            <input class="form-control" name="contents[0][content_title]" type="text" value="{{ $content->title ?? '' }}" required>
                        </div>
                    </div>
                    <div class="flexBox_item">
                        <div class="formBox_full_item">
                            <label class="form-label" for="content_sub_title">Title phụ</label>
                            <input class="form-control" name="contents[0][content_sub_title]" type="text" value="{{ $content->sub_title ?? '' }}">
                        </div>
                    </div>
                </div>
                <!-- One Dual Column -->
                <div class="formBox_full flexBox">
                    <div class="flexBox_item">
                        <div class="formBox_full_item">
                            <label class="form-label inputRequired" for="content_icon">ID Icon</label>
                            <input class="form-control" name="contents[0][content_icon]" type="text" value="{{ $content->icon ?? '' }}" required>
                        </div>
                    </div>
                    <div class="flexBox_item">
                        <div class="formBox_full_item">
                            <label class="form-label" for="content_ordering">Thứ tự</label>
                            <input class="form-control" name="contents[0][content_ordering]" type="number" value="{{ $content->ordering ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="formBox_full">
                    <div class="formBox_full_item">
                        <!-- One Column -->
                        <div>
                            <label class="form-label inputRequired" for="content">Nội dung</label>
                        </div>
                        <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                            <textarea class="form-control tinySelector" name="contents[0][content]" rows="20">{!! $content->content ?? '' !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @pushonce('scriptCustom')
    <!-- Place the first <script> tag in your HTML's <head> -->
    
    
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            callTiny();
        });

    </script>

@endpushonce --}}