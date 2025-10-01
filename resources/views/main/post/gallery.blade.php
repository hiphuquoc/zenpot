@php
    $title = $itemSeo->title ?? $item->seo->title ?? null;
@endphp
<div class="galleryBox js_setWidth">
    <div id="js_setFullWidth" class="galleryBox_box" data-show="0">
        @foreach($item->files as $file)
            @php
                $galleryUrl = \App\Helpers\Image::getUrlImageLargeByUrlImage($file->file_path);
            @endphp
            <div class="galleryBox_box_item js_setFlex">
                <img src="{{ $galleryUrl }}" alt="{{ $title }}" title="{{ $title }}" loading="lazy" />
            </div>
        @endforeach

        <!-- trường hợp không có ảnh -->
        @if($item->files->count()<1)
            <div class="galleryBox_box_item js_setFlex">
                <img src="{{ config('image.image_default_non_image').'?123' }}" alt="{{ $title }}" title="{{ $title }}" loading="lazy" />
            </div>
        @endif
    </div>
    @if($item->files->count()>1)
        <div class="galleryBox_arrow">
            <div class="galleryBox_arrow__privious js_btnPri">
                <svg><use xlink:href="#icon_arrow_left"></use></svg>
            </div>
            <div class="galleryBox_arrow__next js_btnNext">
                <svg><use xlink:href="#icon_arrow_right"></use></svg>
            </div>
        </div>
        <div id="galleryBox_bar" class="galleryBox_bar">
            @foreach($item->files as $file)
                @php
                    $galleryUrl = \App\Helpers\Image::getUrlImageLargeByUrlImage($file->file_path);
                @endphp
                <div data-id="" class="js_clickGalleryBar">
                    <img src="{{ $galleryUrl }}" alt="{{ $title }}" title="{{ $title }}" loading="lazy" />
                </div>
            @endforeach
        </div>
    @endif
</div>

@pushonce('scriptCustom')

    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function() {
            const nameSetFullWidth = document.getElementById('js_setFullWidth');
            const galleryBar = document.getElementById('galleryBox_bar');

            function setImgShowGallery(keyShow = 0) {
                // const pageWidth = document.querySelector('.layoutPageCategoryBlog').offsetWidth;
                // const widthS = window.innerWidth < 768 ? pageWidth : Math.floor(pageWidth * 1);
                const pageWidth = document.querySelector('.layoutPageCategoryBlog_main').offsetWidth;
                const widthS = pageWidth;

                const box = document.querySelector('.js_setWidth');
                const items = document.querySelectorAll('.js_setFlex');
                const countE = nameSetFullWidth.children.length;

                box.style.width = `${widthS}px`;
                nameSetFullWidth.style.width = `${widthS * countE}px`;

                items.forEach(item => {
                    item.style.flex = `0 0 ${widthS}px`;
                });

                const valueTrans = keyShow * widthS;
                nameSetFullWidth.style.transform = `translate3d(-${valueTrans}px, 0, 0)`;
            }

            function hiddenShowBtnPriviousAndNext() {
                const valueShowCur = parseInt(nameSetFullWidth.dataset.show);
                const countE = nameSetFullWidth.children.length;

                document.querySelector('.js_btnNext').style.display = (valueShowCur >= countE - 1) ? 'none' : 'flex';
                document.querySelector('.js_btnPri').style.display = (valueShowCur <= 0) ? 'none' : 'flex';
            }

            function selectedGalleryBar(valueShowCur = null) {
                if (valueShowCur === null) valueShowCur = parseInt(nameSetFullWidth.dataset.show);

                Array.from(galleryBar.children).forEach(child => child.classList.remove('selected'));
                const selectedItem = galleryBar.querySelector(`[data-id="${valueShowCur}"]`);
                if (selectedItem) selectedItem.classList.add('selected');

                const widthChild = galleryBar.children[0]?.offsetWidth + 15 || 0;
                galleryBar.scrollTo({
                    left: (valueShowCur * widthChild),
                    behavior: 'smooth'
                });
            }

            function changeImgShowGallery(type = 'next', valueShowNew = null) {
                let valueShowCur = parseInt(nameSetFullWidth.dataset.show);

                if (valueShowNew === null) {
                    valueShowNew = type === 'next' ? valueShowCur + 1 : valueShowCur - 1;
                }

                nameSetFullWidth.dataset.show = valueShowNew;

                setImgShowGallery(valueShowNew);
                selectedGalleryBar(valueShowNew);
                hiddenShowBtnPriviousAndNext();
            }

            document.querySelector('.js_btnPri').addEventListener('click', () => {
                changeImgShowGallery('previous');
            });

            document.querySelector('.js_btnNext').addEventListener('click', () => {
                changeImgShowGallery('next');
            });

            document.querySelectorAll('.js_clickGalleryBar').forEach((item, index) => {
                item.dataset.id = index;
                item.addEventListener('click', function() {
                    changeImgShowGallery('click', parseInt(this.dataset.id));
                });
            });

            window.addEventListener('resize', () => {
                setImgShowGallery(parseInt(nameSetFullWidth.dataset.show));
            });

            setImgShowGallery(0);
            hiddenShowBtnPriviousAndNext();
            selectedGalleryBar();


            // /* zoom khi hover */
            // const galleryBox = document.querySelector('.galleryBox_box');

            // galleryBox.addEventListener('mousemove', function(event) {
            //     const img = galleryBox.querySelector('.js_setFlex:nth-child(' + (parseInt(nameSetFullWidth.dataset.show) + 1) + ') img');
            //     const { width, height, left, top } = img.getBoundingClientRect();

            //     const offsetX = event.clientX - left;
            //     const offsetY = event.clientY - top;

            //     const percentX = offsetX / width;
            //     const percentY = offsetY / height;

            //     img.style.transformOrigin = `${percentX * 100}% ${percentY * 100}%`;
            //     img.style.transform = 'scale(2)'; // phóng to 2 lần
            // });

            // galleryBox.addEventListener('mouseleave', function() {
            //     const img = galleryBox.querySelector('.js_setFlex:nth-child(' + (parseInt(nameSetFullWidth.dataset.show) + 1) + ') img');
            //     img.style.transform = '';
            // });
        });

    </script>

@endpushonce