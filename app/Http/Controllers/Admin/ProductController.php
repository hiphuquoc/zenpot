<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BuildInsertUpdateModel;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Upload;
use App\Http\Requests\ProductRequest;
use App\Models\Seo;
use App\Models\Tag;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductPrice;
use App\Models\SystemFile;
use App\Models\RelationCategoryProduct;
use App\Models\RelationSeoProductInfo;
use App\Models\Prompt;
use App\Models\SeoContent;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\IndexController;
use App\Models\ProductTranslate;
use App\Jobs\CopyMultiProductJob;

class ProductController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public function createAndUpdate(ProductRequest $request){
        try {
            DB::beginTransaction();
            /* ngôn ngữ */
            $keyTable           = 'product_info';
            $idSeo              = $request->get('seo_id') ?? 0;
            $idSeoVI            = $request->get('seo_id_vi') ?? 0;
            $idProduct          = $request->get('product_info_id');
            $language           = $request->get('language');
            $type               = $request->get('type');
            /* upload image */
            $dataPath           = [];
            if($request->hasFile('image')) {
                $name           = !empty($request->get('slug')) ? $request->get('slug') : time();
                $fileName       = $name.'.'.config('image.extension');
                $folderUpload   =  config('main_'.env('APP_NAME').'.google_cloud_storage.wallpapers');
                $dataPath       = Upload::uploadWallpaper($request->file('image'), $fileName, $folderUpload);
            }
            /* update page & content */
            $seo                = $this->BuildInsertUpdateModel->buildArrayTableSeo($request->all(), $keyTable, $dataPath);
            if(!empty($idSeo)&&$type=='edit'){
                /* insert seo_content => ghi chú quan trọng: vì trong update Item có tính năng replace url thay đổi trong content, nên bắt buộc phải cập nhật content trước để cố định dữ liệu */
                if(!empty($request->get('content'))) CategoryController::insertAndUpdateContents($idSeo, $request->get('content'));
                /* update seo */
                Seo::updateItem($idSeo, $seo);
            }else {
                $idSeo = Seo::insertItem($seo, $idSeoVI);
                /* insert seo_content */
                if(!empty($request->get('content'))) CategoryController::insertAndUpdateContents($idSeo, $request->get('content'));
            }
            /* update những phần khác */
            if($language=='vi'){
                /* insert hoặc update product_info */
                $infoProduct    = $this->BuildInsertUpdateModel->buildArrayTableProductInfo($request->all(), $idSeo);
                if (empty($idProduct)) {
                    // Tạo mới sản phẩm
                    $idProduct = Product::insertItem($infoProduct);
                } else {
                    // Cập nhật sản phẩm
                    Product::updateItem($idProduct, $infoProduct);

                    // Xóa bản dịch cũ (theo ngôn ngữ)
                    ProductTranslate::where('product_info_id', $idProduct)
                        ->where('language', $language)
                        ->delete();
                }

                // Thêm bản dịch mới
                ProductTranslate::insertItem([
                    'product_info_id' => $idProduct,
                    'language'        => $language,
                    'material'        => $request->get('material') ?? '',
                    'usage'           => $request->get('usage') ?? '',
                ]);
                /* lưu tag name */
                TagController::createOrGetTagName($idProduct, 'product_info', $request->get('tag'));
                /* update product_price 
                    => xóa các product_price nào id không tồn tại trong mảng mới 
                    => nào có tồn tại thì update - nào không thì thêm mới 
                */
                $priceSave          = [];
                
                foreach($request->get('prices') as $price){
                    if(!empty($price['id'])) $priceSave[]   = $price['id'];
                }
                $productPriceDelete = ProductPrice::select('*')
                                        ->where('product_info_id', $idProduct)
                                        ->whereNotIn('id', $priceSave)
                                        ->with('files')
                                        ->get();
                /* duyệt mảng delete files */
                foreach($productPriceDelete as $productPrice) {
                    // xóa ảnh của phiên bản này
                    $removeFiles = SystemFile::select('*')
                        ->where('attachment_id', $productPrice->id)
                        ->where('relation_table', 'product_price')
                        ->get();
                    foreach($removeFiles as $removeFile) GalleryController::removeById($removeFile->id);
                    /* xóa product price */
                    $productPrice->delete();
                }
                /* update lại các product price còn lại */
                foreach ($request->get('prices', []) as $key => $price) {
                    // Bỏ qua nếu thiếu dữ liệu cơ bản
                    if (empty($price['code_name']) || empty($price['price'])) {
                        continue;
                    }

                    $isUpdate = !empty($price['id']) && $type === 'edit';
                    $mode     = $isUpdate ? 'update' : 'insert';

                    // Build data cho product_price
                    $dataPrice = $this->BuildInsertUpdateModel->buildArrayTableProductPrice($price, $idProduct, $mode);

                    // Insert / Update
                    if ($isUpdate) {
                        $idPrice = $price['id'];
                        ProductPrice::updateItem($idPrice, $dataPrice);
                        $arrayProductPriceRemain = $price['product_price_file_uploaded'] ?? [];
                        // xóa các ảnh không còn trong mảng (trường hợp này delete input bằng ajax trong html)
                        $productPriceImageDelete = SystemFile::select('*')
                            ->where('attachment_id', $idPrice)
                            ->where('relation_table', 'product_price')
                            ->whereNotIn('id', $arrayProductPriceRemain)
                            ->get();
                        foreach($productPriceImageDelete as $fDelete)  GalleryController::removeById($fDelete->id);
                    } else {
                        $idPrice = ProductPrice::insertItem($dataPrice); // giả sử insertItem trả về id
                    }

                    // Upload ảnh liên quan (nếu có)
                    $uploadedImages = $request->file("prices.$key.product_price_file") ?? [];
                    if (!empty($uploadedImages)) {
                        GalleryController::upload($uploadedImages, [
                            'name'  => 'product_price_file_'.$idPrice,
                            'attachment_id'  => $idPrice,
                            'relation_table' => 'product_price',
                        ]);
                    }
                }
                /* chủ đề */
                RelationCategoryProduct::select('*')
                    ->where('product_info_id', $idProduct)
                    ->delete();
                foreach(config('main_'.env('APP_NAME').'.category_type') as $type){
                    if(!empty($request->all()[$type['key']])){
                        foreach($request->all()[$type['key']] as $idCategory){
                            RelationCategoryProduct::insertItem([
                                'product_info_id'       => $idProduct,
                                'category_info_id'      => $idCategory
                            ]);
                        }
                    }
                }
                /* insert slider và lưu CSDL */
                if($request->hasFile('slider')&&!empty($idProduct)){
                    $name           = !empty($request->get('slug')) ? $request->get('slug') : time();
                    $params         = [
                        'attachment_id'     => $idProduct,
                        'relation_table'    => $keyTable,
                        'name'              => $name
                    ];
                    SliderController::upload($request->file('slider'), $params);
                }
            }
            /* relation_seo_product_info */
            $relationSeoCategoryInfo = RelationSeoProductInfo::select('*')
                                    ->where('seo_id', $idSeo)
                                    ->where('product_info_id', $idProduct)
                                    ->first();
            if(empty($relationSeoCategoryInfo)) RelationSeoProductInfo::insertItem([
                'seo_id'            => $idSeo,
                'product_info_id'   => $idProduct
            ]);
            DB::commit();
            /* Message */
            $message        = [
                'type'      => 'success',
                'message'   => '<strong>Thành công!</strong> Đã cập nhật Sản phẩm!'
            ];
            /* nếu có tùy chọn index => gửi google index */
            if(!empty($request->get('index_google'))&&$request->get('index_google')=='on') {
                $flagIndex = IndexController::indexUrl($idSeo);
                if($flagIndex==200){
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Sản phẩm và Báo Google Index!';
                }else {
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Sản phẩm! <span style="color:red;">nhưng báo Google Index lỗi</span>';
                }
            }
        } catch (\Exception $exception){
            DB::rollBack();
        }
        /* có lỗi mặc định Message */
        if(empty($message)){
            $message        = [
                'type'      => 'danger',
                'message'   => '<strong>Thất bại!</strong> Có lỗi xảy ra, vui lòng thử lại'
            ];
        }
        $request->session()->put('message', $message);
        return redirect()->route('admin.product.view', ['id' => $idProduct, 'language' => $language]);
    }

    public static function view(Request $request){
        $keyTable           = 'product_info';
        $message            = $request->get('message') ?? null;
        $id                 = $request->get('id') ?? 0;
        $language           = $request->get('language') ?? 'vi';
        /* kiểm tra xem ngôn ngữ có nằm trong danh sách không */
        $flagView       = false;
        foreach(config('language') as $ld){
            if($ld['key']==$language) {
                $flagView = true;
                break;
            }
        }
        /* lấy thông tin item */
        $item   = Product::where('product_info.id', $id)
                    ->with([
                        'files' => function($query) use($keyTable){
                            $query->where('relation_table', $keyTable);
                        },
                        'seo',
                        'seos',
                        'prices.files',
                        'categories',
                        'translate' => function($query) use ($language) {
                            $query->where('language', $language);
                        }
                    ])
                    ->first();
        if(empty($item)) $flagView = false;
        if($flagView==true){
            /* chức năng copy source */
            $idSeoSourceToCopy  = $request->get('id_seo_source') ?? 0;
            $itemSourceToCopy   = Product::select('*')
                                    ->whereHas('seos.infoSeo', function($query) use($idSeoSourceToCopy){
                                        $query->where('id', $idSeoSourceToCopy);
                                    })
                                    ->with(['files' => function($query) use($keyTable){
                                        $query->where('relation_table', $keyTable);
                                    }])
                                    ->with('seo', 'seos', 'prices.files', 'categories')
                                    ->first();
            $itemSeoSourceToCopy    = [];
            if(!empty($itemSourceToCopy->seos)){
                foreach($itemSourceToCopy->seos as $s){
                    if(!empty($s->infoSeo->language)&&$s->infoSeo->language==$language) {
                        $itemSeoSourceToCopy = $s->infoSeo;
                        break;
                    }
                }
            }
            /* lấy item seo theo ngôn ngữ được chọn */
            $itemSeo            = [];
            if(!empty($item->seos)){
                foreach($item->seos as $s){
                    if(!empty($s->infoSeo->language)&&$s->infoSeo->language==$language) {
                        $itemSeo = $s->infoSeo;
                        break;
                    }
                }
            }
            /* prompts */
            $prompts            = Prompt::select('*')
                                    ->where('reference_table', $keyTable)
                                    ->orderBy('ordering', 'ASC')
                                    ->get();
            /* gộp lại thành parents và lọc bỏ page hinh-nen-dien-thoai */
            $parents            = Category::all();
            $categories         = $parents;
            /* trang canonical -> cùng là sản phẩm */
            $idProduct          = $item->id ?? 0;
            $sources            = Product::select('*')
                                    ->whereHas('seos.infoSeo', function($query) use($language){
                                        $query->where('language', $language);
                                    })
                                    ->where('id', '!=', $idProduct)
                                    ->get();
            /* tag name */
            $tags               = Tag::all();
            $arrayTag           = [];
            foreach($tags as $tag) if(!empty($tag->seo->title)) $arrayTag[] = $tag->seo->title;
            /* đếm số lượng trang đang chọn trang gốc là trang này => để hiện thị nút "Copy sang trang con" */
            $idSeoVi            = $item->seo->id ?? 0;
            $countChild         = Seo::select('*')
                                    ->where('link_canonical', $idSeoVi)
                                    ->count();
            /* type */
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            /* list danh sách trang chưa đủ content (html) */
            $languageNotEnoughContent = CategoryController::getListPageNotEnoughContent($item);
            return view('admin.product.view', compact('item', 'itemSeo', 'itemSourceToCopy', 'itemSeoSourceToCopy', 'prompts', 'language', 'type', 'categories', 'sources', 'parents', 'arrayTag', 'countChild', 'languageNotEnoughContent', 'message'));
        }else {
            return redirect()->route('admin.product.list');
        }
    }

    public static function list(Request $request){
        $params                         = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* Search theo danh mục */
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        /* paginate */
        $viewPerPage        = Cookie::get('viewProductInfo') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = Product::getList($params);
        $categories = Category::select('*')
                        ->with('products', 'seo')
                        ->get();
        return view('admin.product.list', compact('list', 'categories', 'viewPerPage', 'params'));
    }

    public static function listLanguageNotExists(Request $request){
        $params             = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* Search theo danh mục */
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        /* paginate */
        $viewPerPage        = Cookie::get('viewProductInfoLanguageNotExists') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = Product::listLanguageNotExists($params);
        return view('admin.product.listLanguageNotExists', compact('list', 'params', 'viewPerPage'));
    }

    public static function searchProductCopied(Request $request){
        $xhtml  = '';
        if(!empty($request->get('id_seo'))){
            $idSeo      = $request->get('id_seo');
            $copiedSeos = Product::select('*')
                            ->whereHas('seo', function($query) use($idSeo){
                                $query->where('link_canonical', $idSeo);
                            })
                            ->get();
            $i          = 1;
            foreach($copiedSeos as $item){
                $no     = $i;
                $xhtml .= view('admin.product.row', compact('item', 'no'))->render();
                ++$i;
            }
        }
        echo $xhtml;
    }

    public static function updateProductCopied(Request $request){
        $idSeo      = $request->get('id_seo') ?? 0;
        if(!empty($idSeo)){ /* điều kiện này quan trọng -> vì nếu rỗng sẽ lấy hết sản phẩm */
            /* lấy sản phẩm gốc */
            $productSource = Product::select('*')
                                ->whereHas('seo', function($query) use($idSeo){
                                    $query->where('id', $idSeo);
                                })
                                ->with('seo', 'seos')
                                ->first();
            /* lấy danh sách sản phẩm copy */
            $products   = Product::select('*')
                        ->whereHas('seo', function($query) use($idSeo){
                            $query->where('link_canonical', $idSeo);
                        })
                        ->with('seo', 'seos')
                        ->get();
            // $response   = self::copyMultiProduct($productSource, $products);
            $response       = CopyMultiProductJob::dispatch($productSource, $products);
            $message        = [
                'type'      => 'success',
                'message'   => 'Đã gửi yêu cầu Copy sang trang con thành công! (Job chạy ngầm)',
            ];
            $request->session()->put('message', $message);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return response()->json(['success' => false, 'message' => 'Thiếu ID sản phẩm']);
        }

        try {
            DB::beginTransaction();

            $product = Product::with([
                'seo',
                'seos.infoSeo.contents',
                'prices.files',
                'files',
                'categories',
                'tags',
                'translate'
            ])->find($id);

            if (empty($product)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
            }

            /* =====================
            * 1. XÓA ẢNH TRONG SEO CHÍNH
            * ===================== */
            if (!empty($product->seo->image)) {
                Upload::deleteWallpaper($product->seo->image);
            }

            /* =====================
            * 2. XÓA ẢNH, FILE, SLIDER CỦA PRODUCT_PRICE
            * ===================== */
            foreach ($product->prices as $price) {
                foreach ($price->files as $file) {
                    GalleryController::removeById($file->id);
                }
                $price->delete();
            }

            /* =====================
            * 3. XÓA FILE KHÁC (relation_table = product_info)
            * ===================== */
            foreach ($product->files as $file) {
                GalleryController::removeById($file->id);
            }

            /* =====================
            * 4. XÓA CATEGORIES, TAGS, TRANSLATE
            * ===================== */
            $product->categories()->delete();
            $product->tags()->delete();
            if (!empty($product->translate)) {
                $product->translate()->delete();
            }

            /* =====================
            * 5. XÓA SEO PHỤ (seos.infoSeo + contents)
            * ===================== */
            foreach ($product->seos as $relationSeo) {
                if (!empty($relationSeo->infoSeo)) {
                    // Xóa ảnh SEO phụ
                    if (!empty($relationSeo->infoSeo->image)) {
                        Upload::deleteWallpaper($relationSeo->infoSeo->image);
                    }

                    // Xóa nội dung SEO phụ
                    foreach ($relationSeo->infoSeo->contents ?? [] as $content) {
                        $content->delete();
                    }

                    $relationSeo->infoSeo()->delete();
                }
                $relationSeo->delete();
            }

            /* =====================
            * 6. XÓA SEO CHÍNH (kèm nội dung)
            * ===================== */
            if (!empty($product->seo)) {
                foreach ($product->seo->contents ?? [] as $content) {
                    $content->delete();
                }
                $product->seo()->delete();
            }

            /* =====================
            * 7. XÓA QUAN HỆ RELATION_SEO_PRODUCT_INFO (nếu còn sót)
            * ===================== */
            \App\Models\RelationSeoProductInfo::where('product_info_id', $id)->delete();

            /* =====================
            * 8. XÓA PRODUCT CHÍNH
            * ===================== */
            $product->delete();

            /* =====================
            * 9. XÓA TRONG MEILISEARCH (nếu có)
            * ===================== */
            try {
                $meili = new \Meilisearch\Client(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
                $meili->index('product_info')->deleteDocument($id);
            } catch (\Exception $e) {
                \Log::warning("Meilisearch delete failed for product ID $id: " . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm và toàn bộ dữ liệu liên quan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Xóa sản phẩm lỗi: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi khi xóa sản phẩm']);
        }
    }

    public static function copyMultiProduct($infoProductSource, $arrayInfoProduct){ /* đã chuyển ra job và đang không dùng */
        $response   = []; /* trả ra array id đã xử lý */
        try {
            DB::beginTransaction();
            foreach ($arrayInfoProduct as $t) {
                /* xóa relation seos -> infoSeo -> contents (nếu có) */
                foreach ($t->seos as $seo) {
                    foreach ($seo->infoSeo->contents as $content) {
                        SeoContent::select('*')
                            ->where('id', $content->id)
                            ->delete();
                    }
                    \App\Models\RelationSeoProductInfo::select('*')
                        ->where('seo_id', $seo->seo_id)
                        ->delete();
                    Seo::select('*')
                        ->where('id', $seo->seo_id)
                        ->delete();
                }
                /* tạo dữ liệu mới */
                foreach ($infoProductSource->seos as $seoS) {
                    /* tạo seo */
                    $tmp2   = $seoS->infoSeo->toArray();
                    $insert = [];
                    foreach ($tmp2 as $key => $value) {
                        if ($key != 'contents' && $key != 'id') $insert[$key] = $value;
                    }
                    $insert['link_canonical']   = $tmp2['id'];
                    $insert['slug']             = $tmp2['slug'] . '-' . $t->id;
                    $insert['slug_full']        = $tmp2['slug_full'] . '-' . $t->id;
                    $idSeo = Seo::insertItem($insert);
                    /* cập nhật lại seo_id của product */
                    if ($insert['language'] == 'vi') {
                        Product::updateItem($t->id, [
                            'seo_id' => $idSeo,
                        ]);
                    }
                    $response[] = $idSeo;
                    /* tạo relation_seo_product_info */
                    RelationSeoProductInfo::insertItem([
                        'seo_id'    => $idSeo,
                        'product_info_id' => $t->id,
                    ]);
                    /* tạo content */
                    foreach ($seoS->infoSeo->contents as $content) {
                        $contentInsert = $content->content;
                        $contentInsert = str_replace($seoS->infoSeo->slug_full, $insert['slug_full'], $contentInsert);
                        SeoContent::insertItem([
                            'seo_id'    => $idSeo,
                            'content'   => $contentInsert,
                            'ordering'  => $content->ordering,   
                        ]);
                    }
                }
                /* copy relation product và category */
                \App\Models\RelationCategoryProduct::select('*')
                    ->where('product_info_id', $t->id)
                    ->delete();
                foreach($infoProductSource->categories as $category){
                    \App\Models\RelationCategoryProduct::insertItem([
                        'category_info_id'       => $category->category_info_id,
                        'product_info_id'      => $t->id
                    ]);
                }
                /* copy relation product và tag */
                \App\Models\RelationTagInfoOrther::select('*')
                    ->where('reference_type', 'product_info')
                    ->where('reference_id', $t->id)
                    ->delete();
                foreach($infoProductSource->tags as $tag){
                    \App\Models\RelationTagInfoOrther::insertItem([
                        'tag_info_id'       => $tag->tag_info_id,
                        'reference_type'    => 'product_info',
                        'reference_id'      => $t->id
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $exception){
            DB::rollBack();
        }
        return $response;
    }
}
