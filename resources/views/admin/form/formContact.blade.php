{{-- <input type="hidden" name="seo_id" value="{{ $itemSeo->id ?? null }}" /> --}}
<div class="formBox">
    <div class="formBox_full">
        <div class="formBox_full_item">
            <div class="flexBox">
                <div class="flexBox_item">
                    <!-- One Row -->
                    <div class="formBox_full_item">
                        <label class="form-label inputRequired" for="contact_name">Họ và tên</label>
                        <input type="text" id="contact_name" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="contact_name" value="{{ old('contact_name') ?? $item->contact->name ?? '' }}" required>
                        <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
                    </div>
                </div>
                <div class="flexBox_item">
                    <label class="form-label inputRequired" for="contact_position">Vị trí</label>
                    <input type="text" id="contact_position" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="contact_position" value="{{ old('contact_position') ?? $item->contact->position ?? '' }}" required>
                    <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
                </div>
            </div>
        </div>
        {{-- <div class="formBox_full_item">
            <label class="form-label inputRequired" for="contact_avatar">Ảnh đại diện</label>
            <input class="form-control" type="file" id="contact_avatar" name="contact_avatar" />
        </div> --}}
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="image">Ảnh đại diện</label>
            <input class="form-control" type="file" id="contact_avatar" name="contact_avatar" onchange="readURL(this, 'imageUpload');" />
            <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
            <div class="imageUpload" style="max-width: 100px;margin-top: 1rem;">
                @php
                    $imageUrl       = !empty($item->contact->avatar_file_cloud) ? \App\Helpers\Image::getUrlImageCloud($item->contact->avatar_file_cloud) : null;
                    $response       = !empty($imageUrl) ? Http::get($imageUrl) : null;
                @endphp
                @if($response && $response->ok())
                    <img id="imageUpload" src="{{ $imageUrl }}?{{ time() }}" />
                @else
                    <img id="imageUpload" src="{{ config('image.default') }}" />
                @endif
            </div>
        </div>
        <!-- One Row -->
        <div class="formBox_full_item">
            <div class="flexBox">
                <div class="flexBox_item">
                    <label class="form-label inputRequired" for="contact_phone">Điện thoại</label>
                    <input type="text" id="contact_phone" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="contact_phone" value="{{ old('contact_phone') ?? $item->contact->phone ?? '' }}" required>
                    <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
                </div>
                <div class="flexBox_item" style="margin-left:1rem;">
                   <label class="form-label" for="contact_zalo">Zalo</label>
                    <input type="text" id="contact_zalo" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="contact_zalo" value="{{ old('contact_zalo') ?? $item->contact->zalo ?? '' }}">
                </div>
            </div>
        </div>
        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label" for="contact_email">Email</label>
            <input type="text" id="contact_email" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="contact_email" value="{{ old('contact_email') ?? $item->contact->email ?? '' }}">
        </div>       
    </div>
</div>