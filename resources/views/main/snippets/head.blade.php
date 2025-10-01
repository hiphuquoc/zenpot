<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{{-- @if(Route::is('main.confirm'))
    <meta name="robots" content="noindex,nofollow">
@else
    @if(!empty($index)&&$index=='no')
        <meta name="robots" content="noindex,nofollow">
    @else 
        <meta name="robots" content="index,follow">
    @endif
@endif --}}
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="fragment" content="!" />
@if(!empty($language))
    <meta name="language" content="{{ $language }}" />
@endif
<!-- Dmca -->
<meta name='dmca-site-verification' content='{{ env('DMCA_VALIDATE') }}' />
{{-- <!-- Tối ưu hóa việc tải ảnh từ Google Cloud Storage -->
<link rel="preconnect" href="https://cdn.wallsora.com" crossorigin>
<link rel="dns-prefetch" href="https://cdn.wallsora.com"> --}}
<!-- Favicon -->
<link rel="shortcut icon" href="https://zenpot.storage.googleapis.com/storage/images/favicon-zenpot.webp" type="image/x-icon" />
<!-- view mode -->
<script src="{{ asset('js/viewMode.js') }}" async></script>

<!-- CSS Khung nhìn đầu tiên - Inline Css -->
@stack('cssFirstView')
<!-- Css tải sau -->
@stack('headCustom')
@if(env('APP_ENV')=='local')
    <!-- tải font nếu dev -->
    <style type="text/css">
        @font-face {
            font-family: "SVN-Momento";
            font-style: normal;
            font-weight: 400;
            src: url("{{ asset('fonts/SVN-Momento.ttf') }}") format("truetype");
        }
    </style>
@endif
{{-- @include('main.snippets.fonts') --}}

<!-- START:: ===== GOOGLE FONTS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&display=swap" rel="stylesheet">
<!-- END:: ===== GOOGLE FONTS -->
