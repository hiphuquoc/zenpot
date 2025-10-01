@if(!empty($contents)&&$contents->isNotEmpty())
    @foreach($contents as $content)
        @php
            $idBox          = \App\Helpers\Charactor::convertStrToUrl($content->title);
            // bài do người dùng đăng nên không render qua blade để bảo mật
            $htmlContent    = $content->content ?? null;
        @endphp
        <div id="{{ $idBox }}" class="businessPlanDetailBox_contentBox_item">
            <div class="businessPlanDetailBox_contentBox_item_title">
                <svg><use xlink:href="#{{ $content->icon ?? null }}"></use></svg>
                <h2>{{ $content->title ?? null }}</h2> 
            </div>
            <div class="businessPlanDetailBox_contentBox_item_content">
                {!! $htmlContent !!}
            </div>
        </div>
    @endforeach
@endif

@push('scriptCustom')
    <script type="text/javascript">
        document.querySelectorAll('.timeLine_item_title').forEach(title => {
            title.addEventListener('click', () => {
            const textElement = title.nextElementSibling;
            if (textElement && textElement.classList.contains('timeLine_item_text')) {
                textElement.style.display = 
                textElement.style.display === 'none' ? 'block' : 'none';
            }
            });
        });
  </script>

@endpush