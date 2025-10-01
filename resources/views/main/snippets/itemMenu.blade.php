@if(!empty($itemMenu->seos)&&$itemMenu->seos->isNotEmpty()&&$itemMenu->flag_show==true)
    @foreach($itemMenu->seos as $seo)
        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
            @php
                $title          = $seo->infoSeo->title ?? null;
                $description    = $seo->infoSeo->seo_description ?? null;
                $url            = '/'.$seo->infoSeo->slug_full;
                $iconMenu       = !empty($itemMenu->icon)&&!empty($icon)&&$icon==true ? '<div class="listMenuGroup_icon"><svg><use xlink:href="#'.$itemMenu->icon.'"></use></svg></div>' : null;
                $sign           = !empty($itemMenu->sign) ? '<span class="listMenuGroup_content_title_tag" style="background:#'.config('main_'.env('APP_NAME').'.sign.'.$itemMenu->sign.'.color').'">'.config('main_'.env('APP_NAME').'.sign.'.$itemMenu->sign.'.name').'</span>' : null;
                $active         = !empty($seo->infoSeo->slug)&&$seo->infoSeo->slug==basename(request()->url()) ? 'active' : '';
            @endphp
            <li class="{{ $active }}">
                <a href="{{ $url }}" title="{{ $title }}" class="listMenuGroup">
                    {!! $iconMenu !!}
                    <div class="listMenuGroup_content">
                        <div class="listMenuGroup_content_title">
                            {{ $title }} {!! $sign !!}
                        </div>
                        <div class="listMenuGroup_content_description maxLine_4">{{ $description }}</div>
                    </div>
                </a>
            </li>
            @break
        @endif
    @endforeach
@endif