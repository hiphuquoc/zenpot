<div class="formBox">
    <div class="formBox_full">

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label" for="subtitle_vip">Mô tả ngắn</label>
            <textarea class="form-control" rows="2" id="subtitle_vip" name="subtitle_vip">{{ $item->subtitle_vip ?? null }}</textarea>
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label" for="file_cloud_logo">Logo</label>
            <input class="form-control" type="file" id="file_cloud_logo" name="file_cloud_logo" onchange="readURL(this, 'imageUpload');" />
            <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
            <div class="imageUpload" style="height: 100px;margin-top: 1rem;">
                @php
                    $imageUrl       = !empty($item->file_cloud_logo) ? \App\Helpers\Image::getUrlImageCloud($item->file_cloud_logo) : null;
                @endphp
                <img id="imageUpload" 
                    src="{{ $imageUrl ?? config('image.default') }}" 
                    style="width:fit-content;height:100%;" 
                    onerror="this.src='{{ config('image.default') }}';" />
            </div>
        </div>
        
    </div>
</div>