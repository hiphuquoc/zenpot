@if(!empty($itemSeo->faqs)&&$itemSeo->faqs->isNotEmpty())
    <div class="faqBox" id="cau-hoi-thuong-gap">
        <div class="faqBox_title">
            <h2>{{ config('data_language_1.'.$language.'.question_and_answer') }}</h2> 
        </div>
        <div class="faqBox_box">
            @foreach($itemSeo->faqs as $faq)
                @php
                    $classAdd = $loop->index>0 ? 'hide' : '';
                @endphp
                <div class="faqBox_box_item {{ $classAdd }}">
                    <div class="faqBox_box_item_question">
                        {{ $faq->question ?? null }}
                    </div>
                    <div class="faqBox_box_item_answer">
                        {{ $faq->answer ?? null }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@push('scriptCustom')
    <script type="text/javascript">
        document.querySelectorAll('.faqBox_box_item').forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('hide');
            });
        });
    </script>
@endpush