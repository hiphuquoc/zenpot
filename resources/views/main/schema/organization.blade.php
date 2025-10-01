@php
    use \App\Helpers\Words;
@endphp
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ config('main_'.env('APP_NAME').'.company_name') }}",
        "description": "{{ config('main_'.env('APP_NAME').'.company_description') }}",
        "founder": "{{ config('main_'.env('APP_NAME').'.founder_name') }}",
        "foundingDate": "{{ date('c', strtotime(config('main_'.env('APP_NAME').'.founding'))) }}",
        "address": "{{ config('main_'.env('APP_NAME').'.company_address').', '.config('main_'.env('APP_NAME').'.company_province') }}",
        "url": "{{ env('APP_URL') }}",
        "logo": "{{ config('main_'.env('APP_NAME').'.logo_main') }}",
        "contactPoint": [
            @foreach(config('main_'.env('APP_NAME').'.contacts') as $contact)
                @if($loop->index!=0) 
                    ,
                @endif
                {
                    "@type": "ContactPoint",
                    "telephone": "{{ $contact['phone'] }}",
                    "contactType": "{{ $contact['type'] }}",
                    "areaServed": ["VN"],
                    "availableLanguage": ["Vietnamese"]
                }
            @endforeach
        ],
        "sameAs": [
            @foreach(config('main_'.env('APP_NAME').'.socials') as $social)
                @if($loop->index!=0) 
                    ,
                @endif
                "{{ $social }}"
            @endforeach
        ]
      }
</script>
