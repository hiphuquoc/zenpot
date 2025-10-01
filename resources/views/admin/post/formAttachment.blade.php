<div class="formBox">
    <div class="formBox_full">
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="attachment_title">Tên tài liệu</label>
            <input type="text" id="attachment_title" class="form-control" name="attachment_title" value="" required>
            <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
        </div>
        <div class="formBox_full_item">
            <label class="form-label" for="attachment_file">Đính kèm</label>
            <input class="form-control" type="file" id="attachment_file" name="attachment_file" />
        </div>

         <div class="formBox_full_item">
            <button class="btn btn-icon btn-primary waves-effect waves-float waves-light" type="button" aria-label="Thêm" style="width:100%;" onclick="uploadAttachment();">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <span>Tải tệp lên</span>
            </button>
        </div>

        <div class="formBox_full_item">
            <label class="form-label">Tệp đã tải thành công</label>
            <div class="attachmentBox" id="js_uploadAttachment">
                @if(!empty($itemSeo->attachments)&&$itemSeo->attachments->isNotEmpty())
                    @foreach($itemSeo->attachments as $infoAttachment)
                        @include('admin.post.rowAttachment', compact('infoAttachment'))
                    @endforeach
                @endif
            </div>
        </div>

    </div>
</div>

@push('scriptCustom')
<script type="text/javascript">
    function uploadAttachment() {

        openCloseFullLoading();

        var idSeo           = $('#seo_id').val();
        var attachmentTitle = $('#attachment_title').val();
        var attachmentFile  = $('#attachment_file')[0].files[0];

        if (!attachmentTitle || !attachmentFile) {
            alert('Vui lòng nhập tên tài liệu và chọn tệp đính kèm.');
            return;
        }

        var formData = new FormData();
        formData.append('seo_id', idSeo);
        formData.append('attachment_title', attachmentTitle);
        formData.append('attachment_file', attachmentFile);
        formData.append('_token', '{{ csrf_token() }}'); // Thêm CSRF token ở đây

        $.ajax({
            url: '{{ route("admin.post.uploadAttachment") }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(result) {
                
                $('#js_uploadAttachment').append(result);
                $('#attachment_title').val('');
                $('#attachment_file').val('');

                openCloseFullLoading();
            },
            error: function(xhr) {
                alert('Tải lên thất bại. Vui lòng thử lại.');
            }
        });
    }

    function deleteAttachment(attachmentId) {

        openCloseFullLoading();

        if (!confirm('Bạn có chắc chắn muốn xoá tài liệu này?')) return;

        $.ajax({
            url: '{{ route("admin.post.deleteAttachment") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: attachmentId
            },
            success: function(response) {

                $('#attachment_' + attachmentId).remove(); // Xoá phần tử HTML tương ứng

                openCloseFullLoading();

            },
            error: function() {
                alert('Có lỗi xảy ra khi xoá. Vui lòng thử lại.');
            }
        });
    }
</script>
@endpush

