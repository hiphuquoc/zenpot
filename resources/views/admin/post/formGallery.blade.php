<!-- One Row -->
<div class="formBox_full_item">
    <span data-toggle="tooltip" data-placement="top" title="Đây là ảnh dùng làm slider hiển thị ở phần giới thiệu của dự án">
        <i class="explainInput" data-feather='alert-circle'></i>
        <label class="form-label" style="z-index:1000;">
            Ảnh Gallery 1500*1000px
        </label>
    </span>

    <!-- Input file chọn nhiều ảnh -->
    <input class="form-control" type="file" id="galleries" name="galleries[]" onchange="readURLs(this, 'galleryUpload');" multiple />

    <!-- Vùng preview ảnh -->
    <div id="galleryUpload" class="imageUpload">
        <!-- Ảnh preview từ người dùng (sẽ reset nếu chọn lại) -->
        <div class="previewNewImages"></div>

        <!-- Ảnh hệ thống đã có (không bị xóa khi chọn lại file mới) -->
        <div class="uploadedSystemImages">
            @if(!empty($item->files) && $item->files->count() > 0)
                @foreach($item->files as $file)
                    <div id="js_removeSystemFileById_{{ $file->id }}">
                        <img src="{{ \App\Helpers\Image::getUrlImageSmallByUrlImage($file->file_path) }}" />
                        <i class="fa-solid fa-circle-xmark" onclick="removeSystemFileById({{ $file->id }});"></i>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

@push('scriptCustom')
<script type="text/javascript">

    function removeSystemFileById(idPost){
        $.ajax({
            url         : '{{ route("admin.gallery.remove") }}',
            type        : 'post',
            dataType    : 'html',
            data        : {
                "_token": "{{ csrf_token() }}",
                id_file : idPost,
            }
        }).done(function(data){
            if(data) $('#js_removeSystemFileById_'+idPost).remove();
        });
    }

</script>
@endpush
