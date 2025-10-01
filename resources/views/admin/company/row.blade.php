@php
    $background = !empty($item->type_vip) ? 'background: linear-gradient(135deg, #ffd7000d, rgba(255, 165, 0, .03), rgba(255, 140, 0, .05), #ffd7001f, rgba(255, 165, 0, .05));' : '';
@endphp
<tr style="{!! $background !!}">
    <td style="text-align: center;">{{ $no }}</td>
    <td>
        <div class="oneLine" style="font-size:1.2rem;font-weight:bold;margin-bottom:1rem;">
            {{ $item->seo->title ?? null }}
        </div>
        <div class="oneLine">
            <strong>Mã số thuế</strong>: {{ $item->tax_code ?? null }}
        </div>
        <div class="oneLine">
            <strong>Địa chỉ</strong>: {{ $item->tax_address ?? null }}
        </div>
        <div class="oneLine">
            <strong>Người đại diện</strong>: {{ $item->legal_representative ?? null }} {{ !empty($item->phone) ? '- '.$item->phone : null }}
        </div>
        <div class="oneLine">
            <strong>Email</strong>: {{ !empty($item->email) ? $item->email : '---' }}
        </div>
        <div class="oneLine" style="margin-top: 1rem;">
            <strong>Nghành nghề chính</strong>: {{ !empty($item->main_industry_code) ? $item->main_industry_code : '---' }} - {{ !empty($item->main_industry_text) ? $item->main_industry_text : '---' }}
        </div>
    </td>
    <td>
        <div class="badge" style="font-size:0.95rem;background:#283747;padding:10px 12px;">{{ !empty($item->industries)&&$item->industries->isNotEmpty() ? $item->industries->count().' nghành nghề' : 0 }}</div>
    </td>
    <td>
        <div class="actionBoxOfList">
            <a href="/{{ $item->seo->slug_full ?? null }}" target="_blank">
                <i class="fa-solid fa-eye"></i>
                <div>Xem</div>
            </a>
            <a href="{{ route('admin.company.view', ['id' => $item->id]) }}">
                <i class="fa-solid fa-pen-to-square"></i>
                <div>Sửa</div>
            </a>
        </div>
    </td>
</tr>