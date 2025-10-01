<div class="formBox">
    <div class="formBox_full">
        <!-- One Row -->
        @php
            $chatgptDataAndEvent = [];
            foreach($prompts as $prompt){
                if($language=='vi'){
                    if($prompt->reference_name=='title'&&$prompt->type=='auto_content'){
                        $chatgptDataAndEvent = \App\Helpers\Charactor::generateChatgptDataAndEvent($item, $prompt, $language, 'title');
                        break;
                    }
                }else {
                    if($prompt->reference_name=='title'&&$prompt->type=='translate_content'){
                        $chatgptDataAndEvent = \App\Helpers\Charactor::generateChatgptDataAndEvent($item, $prompt, $language, 'title');
                        break;
                    }
                }
            }
        @endphp
        <div class="formBox_column2_item_row">
            <div class="inputWithNumberChacractor">
                <span data-toggle="tooltip" data-placement="top" title="
                    Đây là Tiêu đề được hiển thị trên website
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label inputRequired" for="title">Tiêu đề Trang</label>
                    @if(!empty($chatgptDataAndEvent['eventChatgpt']))
                        <i class="fa-solid fa-arrow-rotate-left reloadContentIcon" onclick="{{ $chatgptDataAndEvent['eventChatgpt'] ?? null }}"></i>
                    @endif
                </span>
                <div class="inputWithNumberChacractor_count" data-charactor="title">
                    {{ !empty($itemSeo->title) ? mb_strlen($itemSeo->title) : 0 }}
                </div>
            </div>
            <input type="text" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" id="title" name="title" value="{{ old('title') ?? $itemSeo->title ?? null }}" {{ $chatgptDataAndEvent['dataChatgpt'] ?? null }} required>
            <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
        </div>
        @if($language=='vi')
            <!-- One Row -->
            <div class="formBox_full_item">
                <span data-toggle="tooltip" data-placement="top" title="
                    Nhập vào một số để thể hiện độ ưu tiên khi hiển thị cùng các Category khác (Số càng nhỏ càng ưu tiên cao - Để trống tức là không ưu tiên)
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="ordering">Thứ tự</label>
                </span>
                <input type="number" min="0" id="ordering" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="ordering" value="{{ old('ordering') ?? $itemSeo->ordering ?? $itemSource->seo->ordering ?? '' }}">
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <span data-toggle="tooltip" data-placement="top" title="
                    Danh sách các Exchange cha quản lý Tag này
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="exchanges">Exchange cha</label>
                </span>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="exchanges" name="exchanges[]" aria-hidden="true" multiple="true">
                        @if(!empty($exchanges))
                            @foreach($exchanges as $e)
                                @php
                                    $selected = null;
                                    // Kiểm tra nếu có old input
                                    $oldExchanges = old('exchanges', []);
                                    if(in_array($e->id, $oldExchanges)) {
                                        $selected = 'selected';
                                    } else if (!empty($item->exchanges) && $item->exchanges->isNotEmpty()) {
                                        // Kiểm tra trong $item->exchanges
                                        foreach($item->exchanges as $exchangeParent) {
                                            if($e->id == $exchangeParent->infoExchange->id) {
                                                $selected = 'selected';
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                @if(!empty($e->seo))
                                    <option value="{{ $e->id }}" {{ $selected }}>{{ $e->seo->title }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_full_item">
                <span data-toggle="tooltip" data-placement="top" title="
                    Đây là ID của icon hiển thị bổ trợ trên menu chính (Lập trình viên dùng)
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="icon">ID icon</label>
                </span>
                <input type="text" id="icon" class="form-control {{ !empty($flagCopySource)&&$flagCopySource==true ? 'inputSuccess' : '' }}" name="icon" value="{{ old('icon') ?? $item->icon ?? $itemSource->icon ?? '' }}">
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <label class="form-label" for="sign">Đánh dấu</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="sign" name="sign" aria-hidden="true">
                        <option value="">- Chọn loại -</option>
                        @foreach(config('main_'.env('APP_NAME').'.sign') as $sign)
                            @php
                                $selected = '';
                                if($item->sign==$sign['key']) $selected = 'selected';
                            @endphp
                            <option value="{{ $sign['key'] }}" {{ $selected }}>{{ $sign['name'] }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <label class="form-label" for="filter">Bộ lọc</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="filter" name="filter" aria-hidden="true">
                        @foreach(config('main_'.env('APP_NAME').'.filter') as $filter)
                            @php
                                $selected = '';
                                if($item->type_filter==$filter['key']) $selected = 'selected';
                            @endphp
                            <option value="{{ $filter['key'] }}" {{ $selected }}>{{ $filter['name'] }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_full_item">
                <div class="form-check form-check-success">
                    @php
                        if(empty($item)){
                            $flagCheck = !empty($itemSource->flag_show)&&($itemSource->flag_show==1) ? 'checked' : null;
                        }else {
                            $flagCheck = !empty($item->flag_show)&&($item->flag_show==1) ? 'checked' : null;
                        }
                    @endphp
                    <input type="checkbox" class="form-check-input" name="flag_show" {{ $flagCheck }}>
                    <label class="form-check-label" for="flag_show">Cho phép hiển thị trong danh sách</label>
                </div>
            </div>
        @endif
        
    </div>
</div>