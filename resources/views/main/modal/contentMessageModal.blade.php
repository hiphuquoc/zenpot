<div class="modalBox_box_head">
    {{ $title ?? null }}
</div>
<div class="modalBox_box_body">
    {!! $content ?? null !!}
</div>
<div class="modalBox_box_footer">
    <div class="modalBox_box_footer_item button close" onclick="openCloseModal('messageModal');">Đóng</div>
</div>