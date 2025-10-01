<div class="sortBox">
    <div class="sortBox_left">
        <!-- sort by -->
        @php
            $dataSort       = config('main_'.env('APP_NAME').'.sort_type');
            $sortBy         = Cookie::get('sort_by') ?? $dataSort[0]['key'];
            $inputSortBy    = null;
            foreach($dataSort as $sortItem){
                if($sortBy==$sortItem['key']) {
                    $inputSortBy    = '<svg><use xlink:href="#'.$sortItem['icon'].'"></use></svg>
                                            <div class="maxLine_1">'.config('data_language_1.'.$language.'.'.$sortItem['key']).'</div>';
                }
            }
        @endphp
        <div class="selectCustom">
            <div class="selectCustom_text maxLine_1">
                {!! config('data_language_1.'.$language.'.sort_by') !!}
            </div>
            <div class="selectCustom_input maxLine_1">
                {!! $inputSortBy !!}
            </div>
            <div class="selectCustom_box">
                @foreach($dataSort as $sortItem)
                    @php
                        $selected = null;
                        if($sortBy==$sortItem['key']) $selected = 'selected';
                    @endphp
                    <div class="selectCustom_box_item {{ $selected }}" onClick="setSortBy('{{ $sortItem['key'] }}')">
                        <svg><use xlink:href="#{{ $sortItem['icon'] }}"></use></svg>
                        {!! config('data_language_1.'.$language.'.'.$sortItem['key']) !!}
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Chủ đề/phong cách/sự kiện -->
        @include('main.category.selectCustom')
    </div>

    <div class="sortBox_right">
        <div class="sortBox_right_item">
            <!-- số lượng -->
            <span class="quantity maxLine_1">
                <div class="maxLine_1"><span>{{ $total }}</span> sản phẩm</div>
            </span> 
        </div>
    </div>
</div>