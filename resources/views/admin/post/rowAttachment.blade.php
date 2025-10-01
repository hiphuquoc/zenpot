@if(!empty($infoAttachment))
    <div id="attachment_{{ $infoAttachment->id ?? 0 }}" class="attachmentBox_item">
        <div class="attachmentBox_item_title">
            {{ $infoAttachment->title ?? null }}
        </div>
        <a href="{{ \App\Helpers\Image::getUrlImageCloud($infoAttachment->file_cloud) }}" target="_blank" class="attachmentBox_item_extension">
            {{ $infoAttachment->file_name ?? null }}
        </a>
        <div class="attachmentBox_item_close" onclick="deleteAttachment({{ $infoAttachment->id ?? 0 }});">
            <i class="fa-solid fa-xmark"></i>
        </div>
    </div>
@endif