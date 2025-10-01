<div class="formBox">
    <div class="formBox_full">

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="tax_code">Mã số thuế</label>
            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ $item->tax_code ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="title">Tên doanh nghiệp</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $itemSeo->title ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="short_name">Tên viết tắt</label>
            <input type="text" class="form-control" id="short_name" name="short_name" value="{{ $item->short_name ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label inputRequired" for="international_name">Tên tiếng anh</label>
            <input type="text" class="form-control" id="international_name" name="international_name" value="{{ $item->international_name ?? null }}" readonly />
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <div class="flexBox">
                <div class="flexBox_item">
                    <label class="form-label inputRequired" for="issue_date">Ngày thành lập</label>
                    <input type="text" class="form-control" id="issue_date" name="issue_date" value="{{ $item->issue_date ?? null }}" readonly />
                </div>
                <div class="flexBox_item">
                    <label class="form-label inputRequired" for="last_updated">Ngày cập nhật</label>
                    <input type="text" class="form-control" id="last_updated" name="last_updated" value="{{ $item->last_updated ?? null }}" readonly />
                </div>
            </div>
        </div>

        <!-- One Row -->
        <div class="formBox_full_item">
            <label class="form-label" for="type_vip">Loại hồ sơ</label>
            <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                <select class="select2 form-select select2-hidden-accessible" id="type_vip" name="type_vip" aria-hidden="true">
                    @foreach(config('main_'.env('APP_NAME').'.company_type_vip') as $type)
                        @php
                            $selected = '';
                            if($item->type_vip==$type['key']) $selected = 'selected';
                        @endphp
                        <option value="{{ $type['key'] }}" {{ $selected }}>{{ $type['name'] }}</option>
                    @endforeach
                </select>                    
            </div>
        </div>
        
    </div>
</div>