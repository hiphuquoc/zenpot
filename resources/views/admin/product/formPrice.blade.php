<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">
            Phiên bản của sản phẩm
            <i class="fa-solid fa-circle-xmark" data-repeater-delete></i>
        </h4>
    </div>
    <div class="card-body">
        <input type="hidden" name="id" value="{{ $price['id'] ?? null }}" />
        <div class="formBox">
            <div class="formBox_full">
                <div class="formBox_full_item">
                    <div class="flexBox">
                        <div class="flexBox_item">
                            <label class="form-label inputRequired" for="code_name">Tên</label>
                            <input class="form-control" name="code_name" type="text" value="{{ $price['code_name'] ?? null }}" required />
                        </div>
                        <div class="flexBox_item">
                            <label class="form-label inputRequired" for="price">Giá bán (đ)</label>
                            <input class="form-control" name="price" type="number" value="{{ $price['price'] ?? null }}" required />
                        </div>
                    </div>
                </div>
                <div class="formBox_full_item">
                    <label class="form-label inputRequired" for="image">Ảnh</label>
                    <input class="form-control" type="file" id="contact_avatar" name="contact_avatar" onchange="readURL(this, 'imageUpload');" />
                    <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
                    <div class="imageProductPrice">
                        {{-- @php
                            $imageUrl       = !empty($item->contact->avatar_file_cloud) ? \App\Helpers\Image::getUrlImageCloud($item->contact->avatar_file_cloud) : null;
                            $response       = !empty($imageUrl) ? Http::get($imageUrl) : null;
                        @endphp
                        @if($response && $response->ok())
                            <img id="imageUpload" src="{{ $imageUrl }}?{{ time() }}" />
                        @else
                            <img id="imageUpload" src="{{ config('image.default') }}" />
                        @endif --}}
                        <div class="imageProductPrice_item">
                            <img id="imageUpload" src="{{ config('image.default') }}" />
                        </div>
                        <div class="imageProductPrice_item">
                            <img id="imageUpload" src="{{ config('image.default') }}" />
                        </div>
                        <div class="imageProductPrice_item">
                            <img id="imageUpload" src="{{ config('image.default') }}" />
                        </div>
                        <div class="imageProductPrice_item">
                            <img id="imageUpload" src="{{ config('image.default') }}" />
                        </div>
                    </div>
                </div>

            </div>

            

        </div>
    </div>
</div>