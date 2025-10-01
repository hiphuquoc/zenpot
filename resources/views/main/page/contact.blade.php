@php
    $stringHotline = config('main_'.env('APP_NAME').'.hotline');
@endphp

<div id="lien-he-hoptackinhdoanh" class="contacBoxInBottom">
    <div class="contacBoxInBottom_title">
        <h2>Liên hệ ngay với chúng tôi</h2>
    </div>
    <div class="contacBoxInBottom_box">
        <div>Nếu bạn cần tư vấn và hỗ trợ, đừng ngần ngại liên hệ với chúng tôi!</div>
        <div>📞 Hotline: <a href="tel:{{ \App\Helpers\Number::normalizePhoneNumber($stringHotline) }}"><strong>{{ config('main_'.env('APP_NAME').'.hotline') }}</strong></a> (24/7)</div>
        <div>📧 Email: <a href="mailto:{{ config('main_'.env('APP_NAME').'.email') }}"><strong>{{ config('main_'.env('APP_NAME').'.email') }}</strong></a></div>
        <div>💬 Chat trực tiếp trên website</div>
    </div>
</div>