<div class="stickyBox">
    <!-- box liên hệ -->
    @if(!empty($contact))
        @php
            $contactName = $contact->name ?? null;
            $imageAvatar = config('image.icon_default');
            if(!empty($contact->avatar_file_cloud)) {
                $imageAvatar = \App\Helpers\Image::getUrlImageCloud($contact->avatar_file_cloud);
            }      
        @endphp
        <div class="founderBox">
            <div class="founderBox_infoBox">
                <div class="founderBox_infoBox_image">
                    <img src="{{ $imageAvatar }}" alt="{{ $contactName }}" title="{{ $contactName }}" loading="lazy" />
                </div>
                <div class="founderBox_infoBox_info">
                    <div class="founderBox_infoBox_info_title maxLine_1">{{ $contactName }}</div> 
                    <div class="founderBox_infoBox_info_subTile maxLine_1">{{ $contact->position ?? null }}</div>
                </div>
            </div>
            <div class="founderBox_contactBox">
                <div class="founderBox_contactBox_note">
                    Liên hệ để nhận thông tin chi tiết!
                </div>
                <div class="founderBox_contactBox_box">
                    <a href="https://zalo.me/{{ $contact->zalo }}" target="_blank" class="founderBox_contactBox_box_item chatWithZalo">
                        <img src="https://hoptackinhdoanh.storage.googleapis.com/storage/images/logo-transperant.webp" alt="chat Zalo với {{ $contactName }}" title="chat Zalo với {{ $contactName }}" loading="lazy" />
                        <div>Chat qua Zalo</div>
                    </a>
                    <a href="tel:{{ $contact->phone }}" class="founderBox_contactBox_box_item phoneNumber">
                        <svg><use xlink:href="#icon_phone_volume"></use></svg>
                        <div>{{ $contact->phone ?? '---' }}</div>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- mục lục -->
    @if(empty($contents)||$contents->count()>=2)
        <div class="sidebarBox hide-990">
            <div class="sidebarBox_title">
                Mục lục
            </div>
            <div class="sidebarBox_box">
                <div class="tocContentSidebar">
                    @foreach($contents as $content)
                        @php
                            $idBox = \App\Helpers\Charactor::convertStrToUrl($content->title);
                        @endphp
                        <a href="#{{ $idBox }}" class="tocContentSidebar_item">
                            <svg><use xlink:href="#{{ $content->icon ?? null }}"></use></svg>
                            <div>{{ $content->title ?? null }}</div>
                        </a>
                    @endforeach
                    <!-- tài liệu đính kèm -->
                    @if(!empty($itemSeo->attachments)&&$itemSeo->attachments->isNotEmpty())
                        <a href="#tai-lieu-dinh-kem" class="tocContentSidebar_item">
                            <svg><use xlink:href="#icon_paperclip"></use></svg>
                            <div>Tài liệu đính kèm</div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>