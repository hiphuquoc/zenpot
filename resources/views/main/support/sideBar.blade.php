<!-- Dịch vụ-->
@if(!empty($sidebarPages)&&$sidebarPages->isNotEmpty())
    <div class="sidebarBox">
        <div class="sidebarBox_title">
            <svg><use xlink:href="#icon_support"></use></svg>
            Hỗ trợ
        </div>
        <div class="sidebarBox_box">

            <div class="pageListBox">
                @foreach($sidebarPages as $page)
                    @foreach($page->seos as $seo)
                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                            @php
                                $title  = $seo->infoSeo->title ?? null;
                                $active = !empty($seo->infoSeo->slug)&&$seo->infoSeo->slug==basename(request()->url()) ? 'active' : '';
                            @endphp
                            <a href="/{{ $seo->infoSeo->slug_full }}" title="{{ $title }}" class="pageListBox_item {{ $active }}">
                                {{ $title }}
                            </a>
                            @break
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
@endif