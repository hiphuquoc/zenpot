<div class="blogRelatedBox {{ $blogs->count()<=1 ? 'single-item' : '' }}">
    @foreach($blogs as $blog)
        @foreach($blog->seos as $seo)
            @if(!empty($seo->infoSeo->language) && $seo->infoSeo->language == $language)
                @php
                    $title = $seo->infoSeo->title ?? '';
                    $urlArticle = env('APP_URL').'/'.$seo->infoSeo->slug_full;
                @endphp
                <div class="blogRelatedBox_item">
                    <a href="{{ $urlArticle }}" class="blogRelatedBox_item_image">
                        @php
                            $imageMini  = \App\Helpers\Image::getUrlImageMiniByUrlImage($blog->seo->image);
                            $imageSmall = \App\Helpers\Image::getUrlImageSmallByUrlImage($blog->seo->image);
                        @endphp
                        @if(!empty($blog->seo->image))
                            <img 
                                class="lazyload" 
                                src="{{ $imageMini }}" 
                                data-src="{{ $imageSmall }}" 
                                alt="{{ $title }}" 
                                title="{{ $title }}" 
                                loading="lazy"
                            />
                        @endif
                    </a>
                    <div class="blogRelatedBox_item_content">
                        <a href="{{ $urlArticle }}" class="blogRelatedBox_item_content_title">
                            <div class="maxLine_3">{{ $title }}</div>
                        </a>

                        <div class="blogRelatedBox_item_content_tags">
                            <div class="blogRelatedBox_item_content_tags_item">
                                <svg><use xlink:href="#icon_clock_bold"></use></svg>
                                {{ date('d \t\h\á\n\g m, Y', strtotime($seo->infoSeo->created_at)) }}
                            </div>
                            {{-- <div class="blogRelatedBox_item_content_tags_item">
                                <svg style="transform: scale(1.15)">
                                    <use xlink:href="#icon_eye_bold"></use>
                                </svg>
                                <div>{{ \App\Helpers\Number::formatViews($blog->viewed) }}</div>
                            </div> --}}
                        </div>

                        <div class="blogRelatedBox_item_content_desc maxLine_4">
                            {!! !empty($seo->infoSeo->contents[0]->content) ? strip_tags($seo->infoSeo->contents[0]->content) : '' !!}
                        </div>
                    </div>
                </div>
                @php break; @endphp
            @endif
        @endforeach
    @endforeach
</div>