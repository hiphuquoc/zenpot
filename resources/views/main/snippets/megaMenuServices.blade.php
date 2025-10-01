@php
   $xhtmlBodyMenu = '';
@endphp     

<div class="megaMenu">
   <div class="megaMenu_title">
      <ul>
            @php
                $i = 0;
            @endphp
            @foreach($categories as $category)
                  @foreach($category->seos as $seo)
                        @if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language)
                              @php
                                    $classSelected = $i==0 ? 'selected' : null;
                                    ++$i;
                                    // sắp xếp trước khi duyệt
                                    $sortedTags = $category->tags->sortByDesc(function ($tag) {
                                          return $tag->infoTag->seo->ordering ?? 0;
                                    });
                                    // duyệt lấy kết quả
                                    $xhtmlBodyMenu .= '<ul data-menu="menu_'.$category->id.'">';
                                    foreach($sortedTags as $tag){
                                          $xhtmlBodyMenu .= view('main.snippets.itemMenu', [
                                                'itemMenu'  => $tag->infoTag,
                                                'icon'      => true,
                                                'language'  => $language,
                                          ])->render();
                                    }
                                    $xhtmlBodyMenu .= '</ul>';
                              @endphp
                              <li id="menu_{{ $category->id ?? null }}" onmouseover="openMegaMenu(this.id);" class="{{ $classSelected }}">
                                    <div>{{ $seo->infoSeo->title ?? null }}</div>
                              </li>
                              @break
                        @endif
                  @endforeach
            @endforeach
      </ul>
   </div>
   <div class="megaMenu_content">
      {!! $xhtmlBodyMenu !!}
   </div>
</div>