<div class="formBox">
    <div class="formBox_full">
        <div class="flexBox">
            <!-- one item -->
            <div class="flexBox_item">
                <label class="form-label" for="size">Kích thước - Vd: 15cm x 15cm x 10cm (DxRxC)</label>
                <input type="text" class="form-control{{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="size" name="size" value="{{ old('size') ?? $item->size ?? null }}">
                <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
            </div>
            <!-- one item -->
            <div class="flexBox_item">
                <label class="form-label" for="capacity">Dung tích chỗ trồng (lít)</label>
                <input type="text" class="form-control{{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="capacity" name="capacity" value="{{ old('capacity') ?? $item->capacity ?? null }}">
                <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
            </div>
        </div>
    </div>
    <div class="formBox_full">
        <div class="flexBox">
            <!-- one item -->
            <div class="flexBox_item">
                <label class="form-label" for="weight">Khối lượng (kg)</label>
                <input type="text" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="weight" name="weight" value="{{ old('weight') ?? $item->weight ?? null }}">
                <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
            </div>
            <!-- one item -->
            <div class="flexBox_item">
                <label class="form-label">Tình trạng</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" name="condition">
                        @foreach(config('main_'.env('APP_NAME').'.condition') as $condition)
                            @php
                                $selected   = null;
                                if(old('condition') == $condition['key'] || (!empty($item->condition) && $item->condition == $condition['key'])) {
                                    $selected = 'selected';
                                }
                            @endphp
                            <option value="{{ $condition['key'] }}" {{ $selected }}>{{ $condition['name'] }}</option>
                        @endforeach
                    </select> 
                </div>
            </div>
        </div>
    </div>
    <div class="formBox_full">
        <label class="form-label" for="material">Chất liệu</label>
        <input type="text" class="form-control{{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="material" name="material" value="{{ old('material') ?? $item->translate->material ?? null }}">
        <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    </div>
    <div class="formBox_full">
        <label class="form-label" for="usage">Ứng dụng</label>
        <input type="text" class="form-control{{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="usage" name="usage" value="{{ old('usage') ?? $item->translate->usage ?? null }}">
        <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    </div>
</div>
{{-- @push('scriptCustom')
    <script type="text/javascript">
        
    </script>
@endpush --}}