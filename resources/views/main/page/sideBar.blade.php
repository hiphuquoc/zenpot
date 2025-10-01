<!-- Dịch vụ-->
@if(!empty($services)&&$services->isNotEmpty())
    @foreach($services as $serviceGroup)
        @foreach($serviceGroup->seos as $seo)
            @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                <div class="sidebarBox">
                    <div class="sidebarBox_title">
                        Dịch vụ {{ $seo->infoSeo->title ?? null }}
                    </div>
                    <div class="sidebarBox_box">
                        <ul class="listServices">
                            @foreach($serviceGroup->tags as $serviceDetail)
                                @include('main.snippets.itemMenu', [
                                    'itemMenu'  => $serviceDetail->infoTag,
                                ])
                            @endforeach
                        </ul>

                    </div>
                </div>
                @break
            @endif
        @endforeach
    @endforeach
@endif