<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">
            Phiên bản của sản phẩm
            <i class="fa-solid fa-circle-xmark" data-repeater-delete></i>
        </h4>
    </div>
    <div class="card-body">
        <input type="hidden" name="id" value="{{ $price['id'] ?? null }}" />
        <div class="formBox">
            <div class="formBox_full">
                <div class="formBox_full_item">
                    <div class="flexBox">
                        <div class="flexBox_item">
                            <label class="form-label inputRequired" for="code_name">Tên</label>
                            <input class="form-control" name="code_name" type="text" value="{{ $price['code_name'] ?? null }}" required />
                        </div>
                        <div class="flexBox_item">
                            <label class="form-label inputRequired" for="price">Giá bán (đ)</label>
                            <input class="form-control" name="price" type="number" value="{{ $price['price'] ?? null }}" required />
                        </div>
                    </div>
                </div>
                @if(!empty($item->id))
                    <div class="formBox_full_item">
                        <label class="form-label inputRequired">Ảnh</label>
                        <input class="form-control" type="file" 
                                name="product_price_file" 
                                multiple
                                onchange="handleProductPriceImageChange(this);" />
                        <div class="invalid-feedback">{{ config('message.admin.validate.not_empty') }}</div>
                        <div class="imageProductPrice" id="js_loadProductPriceImage_{{ $price->id }}">
                            <!-- tải ajax -->
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@pushonce('scriptCustom')
    <script type="text/javascript">

        document.addEventListener("DOMContentLoaded", function(){
            loadProductPriceImage();
        });

        // Lắng nghe click trên document, check nếu là nút remove
        document.addEventListener("click", function(e) {
            if (e.target.closest(".imageProductPrice_item_removeIcon")) {
                const item = e.target.closest(".imageProductPrice_item");
                if (item) item.remove();
            }
        });

        function handleProductPriceImageChange(input) {
            const files = input.files;
            if (!files || files.length === 0) return;

            // Tìm card cha để đảm bảo không lẫn repeater khác
            const card = input.closest('.card');
            const previewBox = card.querySelector('.imageProductPrice');

            if (!previewBox) return;

            // ❌ Không xóa previewBox.innerHTML nữa
            // Vì xóa thì sẽ mất luôn ảnh cũ load từ Ajax (DB)

            // Duyệt qua files và render preview
            Array.from(files).forEach(file => {
                if (!file.type.startsWith("image/")) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement("div");
                    wrapper.classList.add("imageProductPrice_item", "is-preview");

                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.classList.add("preview-img");

                    // thêm input hidden tạm để submit file upload mới
                    // (tuỳ backend bạn xử lý FormData file upload thế nào, nếu dùng input[type=file] thì hidden này có thể bỏ)
                    
                    const removeIcon = document.createElement("div");
                    removeIcon.classList.add("imageProductPrice_item_removeIcon");
                    removeIcon.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeIcon);

                    // Thêm vào đầu box (trước ảnh cũ)
                    previewBox.prepend(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        // load ajax mới lấy đúng được name của input
        function loadProductPriceImage(cardElement) {
            const cards = cardElement ? [cardElement] : Array.from(document.querySelectorAll('.card'));

            cards.forEach(card => {
                const inputs = Array.from(card.querySelectorAll('input[name]'));
                if (!inputs.length) return;

                let idInput = inputs.find(i => {
                    const name = i.getAttribute('name') || '';
                    return name === 'id' || name.endsWith('[id]');
                });
                if (!idInput) {
                    idInput = inputs.find(i => i.type === 'hidden' && /^\d+$/.test((i.value || '').toString()));
                }
                if (!idInput || !idInput.value) return;
                const idProductPrice = idInput.value.toString();

                const sampleInput = inputs.find(i => i !== idInput && (i.getAttribute('name') || '').includes('[')) || idInput;
                const fullName = sampleInput.getAttribute('name') || '';
                const prefixNameInput = fullName.replace(/\[[^\]]+\]$/, '') || fullName;

                const previewBox = card.querySelector(`#js_loadProductPriceImage_${idProductPrice}`);
                if (!previewBox) return;

                const fd = new FormData();
                fd.append('product_price_id', idProductPrice);
                fd.append('prefix_name_input', prefixNameInput);
                fd.append('_token', "{{ csrf_token() }}");

                fetch("{{ route('admin.productPrice.loadImageForProductPrice') }}", {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.text())
                .then(html => {
                    previewBox.innerHTML = html;
                })
                .catch(err => {
                    console.error('loadProductPriceImage error:', err);
                });
            });
        }
        
    </script>
@endPushonce