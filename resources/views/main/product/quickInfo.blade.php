<div class="quick-info">
    <div class="quick-info-row">
        <div class="quick-info-label">Mã sản phẩm</div>
        <div class="quick-info-value">{{ $item->code ?? '--' }}</div>
    </div>
    <div class="quick-info-row">
        <div class="quick-info-label">Kích thước (phủ bì)</div>
        <div class="quick-info-value">{{ $item->size ?? '--' }}</div>
    </div>
    <div class="quick-info-row">
        <div class="quick-info-label">Dung tích chỗ trồng</div>
        <div class="quick-info-value">{{ $item->capacity ?? '--' }}</div>
    </div>
    <div class="quick-info-row">
        <div class="quick-info-label">Khối lượng</div>
        <div class="quick-info-value">{{ $item->weight ?? '--' }} kg</div>
    </div>
    <div class="quick-info-row">
        <div class="quick-info-label">Chất liệu</div>
        <div class="quick-info-value">{{ $item->translate->material ?? '--' }}</div>
    </div>
    <div class="quick-info-row">
        <div class="quick-info-label">Ứng dụng</div>
        <div class="quick-info-value">{{ $item->translate->usage ?? '--' }}</div>
    </div>
    <div class="quick-info-row">
        @php
            $conditionText = '--';
            foreach(config('main_'.env('APP_NAME').'.condition') as $c){
                if(!empty($item->condition)??$item->condition==$c['key']) {
                    $conditionText = $c['name'];
                    break;
                }
            }
        @endphp
        <div class="quick-info-label">Tình trạng</div>
        <div class="quick-info-value in-stock">{{ $conditionText }}</div>
    </div>
</div>