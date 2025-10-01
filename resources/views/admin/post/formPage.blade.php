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
            <div class="formBox_column2_item_row">
                <span data-toggle="tooltip" data-placement="top" title="
                    Danh sách các Exchange mà Post này thuộc
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="exchanges">Danh mục</label>
                </span>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="exchanges" name="exchanges[]" aria-hidden="true" multiple="true">
                        @if(!empty($parents))
                            @foreach($parents as $c)
                                @php
                                    $selected = null;
                                    // Kiểm tra nếu có old input
                                    $oldExchanges = old('exchanges', []);
                                    if(in_array($c->id, $oldExchanges)) {
                                        $selected = 'selected';
                                    } else if (!empty($item->exchanges) && $item->exchanges->isNotEmpty()) {
                                        // Kiểm tra trong $item->exchanges
                                        foreach($item->exchanges as $exchange) {
                                            if(!empty($exchange->infoExchangeInfo->id)&&$c->id==$exchange->infoExchangeInfo->id) {
                                                $selected = 'selected';
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <option value="{{ $c->id }}" {{ $selected }}>{{ $c->seo->title }}</option>
                            @endforeach
                        @endif
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <span data-toggle="tooltip" data-placement="top" title="
                    Danh sách các Exchange Tag mà Post này thuộc
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="exchangeTags">Tags</label>
                </span>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="exchangeTags" name="exchangeTags[]" aria-hidden="true" multiple="true">
                        @if(!empty($exchangeTags))
                            @foreach($exchangeTags as $c)
                                @php
                                    $selected = null;
                                    // Kiểm tra nếu có old input
                                    $oldExchangeTags = old('exchangeTags', []);
                                    if(!empty($c->id)&&in_array($c->id, $oldExchangeTags)) {
                                        $selected = 'selected';
                                    } else if (!empty($item->exchangeTags) && $item->exchangeTags->isNotEmpty()) {
                                        // Kiểm tra trong $item->exchangeTags
                                        foreach($item->exchangeTags as $exchangeTag) {
                                            if(!empty($exchangeTag->infoExchangeTag->id)&&$c->id == $exchangeTag->infoExchangeTag->id) {
                                                $selected = 'selected';
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <option value="{{ $c->id }}" {{ $selected }}>{{ $c->seo->title }}</option>
                            @endforeach
                        @endif
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <span data-toggle="tooltip" data-placement="top" title="
                    Danh sách các Exchange Tag mà Post này muốn hiển thị nổi bật
                ">
                    <i class="explainInput" data-feather='alert-circle'></i>
                    <label class="form-label" for="exchangeOutstandings">Tags nổi bật</label>
                </span>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="exchangeOutstandings" name="exchangeOutstandings[]" aria-hidden="true" multiple="true">
                        @if(!empty($exchangeTags))
                            @foreach($exchangeTags as $c)
                                @php
                                    $selected = null;
                                    // Kiểm tra nếu có old input
                                    $oldExchangeOutstandings = old('exchangeOutstandings', []);
                                    if(!empty($c->id)&&in_array($c->id, $oldExchangeOutstandings)) {
                                        $selected = 'selected';
                                    } else if (!empty($item->exchangeOutstandings) && $item->exchangeOutstandings->isNotEmpty()) {
                                        // Kiểm tra trong $item->exchangeTags
                                        foreach($item->exchangeOutstandings as $exchangeOutstanding) {
                                            if(!empty($exchangeOutstanding->infoExchangeTag->id)&&$c->id==$exchangeOutstanding->infoExchangeTag->id) {
                                                $selected = 'selected';
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <option value="{{ $c->id }}" {{ $selected }}>{{ $c->seo->title }}</option>
                            @endforeach
                        @endif
                    </select>                    
                </div>
            </div>
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
                <label class="form-label" for="status">Trạng thái</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="status" name="status" aria-hidden="true">
                        @foreach(config('main_hoptackinhdoanh.post_status') as $status)
                            @php
                                $selected = null;
                                if($item->status==$status['key']) $selected = 'selected';
                            @endphp
                            <option value="{{ $status['key'] }}" {{ $selected }}>{{ $status['name'] }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <label class="form-label" for="type_vip">Loại VIP</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="type_vip" name="type_vip" aria-hidden="true">
                        @foreach(array_reverse(config('main_hoptackinhdoanh.post_type_vip')) as $typeVip)
                            @php
                                $selected = null;
                                if($item->type_vip==$typeVip['key']) $selected = 'selected';
                            @endphp
                            <option value="{{ $typeVip['key'] }}" {{ $selected }}>{{ $typeVip['name'] }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_column2_item_row">
                <label class="form-label" for="ribbon">Đánh dấu</label>
                <div class="{{ !empty($flagCopySource)&&$flagCopySource==true ? 'boxInputSuccess' : '' }}">
                    <select class="select2 form-select select2-hidden-accessible" id="ribbon" name="ribbon" aria-hidden="true">
                        {{-- <option value="">- Chọn loại -</option> --}}
                        @foreach(array_reverse(config('main_hoptackinhdoanh.post_ribbon')) as $ribbon)
                            @php
                                $selected = null;
                                if($item->ribbon==$ribbon['key']) $selected = 'selected';
                            @endphp
                            <option value="{{ $ribbon['key'] }}" {{ $selected }}>{{ $ribbon['name'] }}</option>
                        @endforeach
                    </select>                    
                </div>
            </div>
            <!-- One Row -->
            <div class="formBox_full_item">
                <div class="form-check form-check-success">
                    @php
                        if(empty($item)){
                            $flagCheck = !empty($itemSource->outstanding)&&($itemSource->outstanding==1) ? 'checked' : null;
                        }else {
                            $flagCheck = !empty($item->outstanding)&&($item->outstanding==1) ? 'checked' : null;
                        }
                    @endphp
                    <input id="outstanding" type="checkbox" class="form-check-input" name="outstanding" {{ $flagCheck }}>
                    <label class="form-check-label" for="outstanding">Bài viết nổi bật</label>
                </div>
            </div>
        @endif
        
    </div>
</div>