<div class="formBox">
    <div class="formBox_full">

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="legal_representative">Đại diện</label>
            <input type="text" class="form-control" id="legal_representative" name="legal_representative" value="{{ $item->legal_representative ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="tax_address">Địa chỉ</label>
            <textarea class="form-control" rows="3" id="tax_address" name="tax_address" readonly>{{ $item->tax_address ?? null }}</textarea>
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="phone">Điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $item->phone ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" value="{{ $item->email ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label" for="website">Website</label>
            <input type="text" class="form-control" id="website" name="website" value="{{ $item->website ?? null }}" />
        </div>
    </div>
</div>