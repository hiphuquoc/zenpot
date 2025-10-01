<!-- item -->
<input type="hidden" class="form-control" name="id" value="{{ is_array($service) ? ($service['id'] ?? '') : ($service->id ?? '') }}" required>
<!-- item -->
<div class="flexBox">
    <div class="flexBox_item">
        <label class="form-label inputRequired">Tiêu đề</label>
        <input type="text" class="form-control" name="title" value="{{ is_array($service) ? ($service['title'] ?? '') : ($service->title ?? '') }}" required>
        <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    </div>
    <div class="flexBox_item">
        <label class="form-label inputRequired">Đường dẫn</label>
        <input type="text" class="form-control" name="url" value="{{ is_array($service) ? ($service['url'] ?? '') : ($service->url ?? '') }}" required>
        <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    </div>
</div>
<!-- item -->
<div class="flexBox">
    <div class="flexBox_item">
        <label class="form-label inputRequired">Mô tả</label>
        <textarea type="text" class="form-control" name="description"required>{{ is_array($service) ? ($service['description'] ?? '') : ($service->description ?? '') }}</textarea>
        <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    </div>
</div>
<!-- item -->
<div class="flexBox">
    <div class="flexBox_item">
        <label class="form-label">Ảnh thumnail</label>
        <input class="form-control" type="file" name="image" onchange="readURL(this, 'serviceImageUpload');" />
        <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
        <div class="serviceImageUpload" style="height: 100px;margin-top: 1rem;">
            @php
                $imageUrl       = !empty($service->image) ? \App\Helpers\Image::getUrlImageCloud($service->image) : null;
            @endphp
            <img src="{{ $imageUrl ?? config('image.default') }}" 
                    style="width:fit-content;height:100%;" 
                    onerror="this.src='{{ config('image.default') }}';" />
        </div>
    </div>
</div>
<!-- item -->
<div class="flexBox">
    <div class="flexBox_item">
        <button type="button" class="btn btn-danger waves-effect waves-float waves-light" style="float:right;" data-repeater-delete>Xóa</button>
    </div>
</div>