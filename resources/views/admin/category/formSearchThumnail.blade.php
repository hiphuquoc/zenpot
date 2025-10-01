<!-- form modal chọn wallpaper -->
<form id="formSearchThumnails" method="POST" action="#">
    @csrf
    <div class="modal fade" id="modalSearchThumnails" tabindex="-1" aria-labelledby="addNewCardTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="margin:0 auto;">
            <div class="modal-content">
                <div class="modal-body">
                    
                    <div class="searchViewBefore">
                        <div class="searchViewBefore_input">
                            <!-- value = null không lưu giá trị search cũ -->
                            <input type="text" placeholder="Tìm thumnail..." value="" data-product-price-id="{{ 0 }}" onkeyup="searchWallpapersWithDelay(this)" autocomplete="off" disabled />
                            <div>
                                <svg><use xlink:href="#icon_search"></use></svg>
                            </div>
                        </div>
                        <div id="js_seachFreeWallpaperOfCategory_idWrite" class="searchViewBefore_selectbox">
                            <!-- load ajax -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>


@pushonce('scriptCustom')
    <script type="text/javascript">

        
    </script>
@endpushonce