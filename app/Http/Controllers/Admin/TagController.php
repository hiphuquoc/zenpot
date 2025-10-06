<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BuildInsertUpdateModel;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Upload;
use App\Http\Requests\TagRequest;
use App\Models\Seo;
use App\Models\Prompt;
use App\Models\Tag;
use App\Models\Category;
use App\Models\FAQ;
use App\Models\RelationCategoryInfoTagInfo;
use App\Models\RelationSeoTagInfo;
use App\Models\RelationTagInfoOrther;
use App\Helpers\Charactor;

use Laravel\Scout\EngineManager;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Support\Facades\Log;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;

class TagController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public static function list(Request $request){
        $params             = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* Search theo danh mục */
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        /* paginate */
        $viewPerPage        = Cookie::get('viewTagInfo') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = Tag::getList($params);
        $categories         = Category::select('*')
                                ->get();
        return view('admin.tag.list', compact('list', 'categories', 'params', 'viewPerPage'));
    }

    public static function listLanguageNotExists(Request $request){
        $params             = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* Search theo danh mục */
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        /* paginate */
        $viewPerPage        = Cookie::get('viewTagInfoLanguageNotExists') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = Tag::listLanguageNotExists($params);
        return view('admin.tag.listLanguageNotExists', compact('list', 'params', 'viewPerPage'));
    }

    public static function view(Request $request){
        $keyTable           = 'tag_info';
        $message            = $request->get('message') ?? null;
        $id                 = $request->get('id') ?? 0;
        $language           = $request->get('language') ?? null;
        /* kiểm tra xem ngôn ngữ có nằm trong danh sách không */
        $flagView       = false;
        foreach(config('language') as $ld){
            if($ld['key']==$language) {
                $flagView = true;
                break;
            }
        }
        /* tìm theo ngôn ngữ */
        $item               = Tag::select('*')
                                ->where('id', $id)
                                ->with('seo', 'seos')
                                ->first();
        if(empty($item)) $flagView = false;
        if($flagView==true){
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
            $parents            = Category::all();
            /* categories cha */
            $categories         = Category::all();
            /* type */
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            /* đếm số lượng trang đang chọn trang gốc là trang này => để hiện thị nút "Copy sang trang con" */
            $idSeoVi            = $item->seo->id ?? 0;
            $countChild         = Seo::select('*')
                                    ->where('link_canonical', $idSeoVi)
                                    ->count();
            return view('admin.tag.view', compact('item', 'itemSeo', 'prompts', 'type', 'language', 'parents', 'categories', 'message'));
        }else {
            return redirect()->route('admin.tag.list');
        }
    }

    public function createAndUpdate(TagRequest $request){
        try {
            DB::beginTransaction();
            /* ngôn ngữ */
            $keyTable           = 'tag_info';
            $idSeo              = $request->get('seo_id') ?? 0;
            $idSeoVI            = $request->get('seo_id_vi') ?? 0;
            $idTag              = $request->get('tag_info_id');
            $language           = $request->get('language');
            $type               = $request->get('type');
            /* check xem là create seo hay update seo */
            $action             = !empty($idSeo)&&$type=='edit' ? 'edit' : 'create';
            /* upload image */
            $dataPath           = [];
            if($request->hasFile('image')) {
                $name           = !empty($request->get('slug')) ? $request->get('slug') : time();
                $fileName       = $name.'.'.config('image.extension');
                $folderUpload   =  config('main_'.env('APP_NAME').'.google_cloud_storage.images');
                $dataPath       = Upload::uploadWallpaper($request->file('image'), $fileName, $folderUpload);
            }
            /* update page & content */
            $seo                = $this->BuildInsertUpdateModel->buildArrayTableSeo($request->all(), $keyTable, $dataPath);
            if($action=='edit'){
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
                /* insert hoặc update tag_info */
                $flagShow           = !empty($request->get('flag_show'))&&$request->get('flag_show')=='on' ? 1 : 0;
                if(empty($idTag)){ /* check xem create tag hay update tag */
                    $idTag          = Tag::insertItem([
                        'flag_show' => $flagShow,
                        'seo_id'    => $idSeo,
                    ]);
                }else {
                    Tag::updateItem($idTag, [
                        'flag_show' => $flagShow,
                    ]);
                }
                /* insert relation_category_info_tag_info */
                RelationCategoryInfoTagInfo::select('*')
                    ->where('tag_info_id', $idTag)
                    ->delete();
                if(!empty($request->get('categories'))){
                    foreach($request->get('categories') as $idCategoryInfo){
                        RelationCategoryInfoTagInfo::insertItem([
                            'category_info_id'  => $idCategoryInfo,
                            'tag_info_id'       => $idTag
                        ]);
                    }
                }
            }
            /* relation_seo_tag_info */
            $relationSeoTagInfo = RelationSeoTagInfo::select('*')
                                    ->where('seo_id', $idSeo)
                                    ->where('tag_info_id', $idTag)
                                    ->first();
            if(empty($relationSeoTagInfo)) RelationSeoTagInfo::insertItem([
                'seo_id'        => $idSeo,
                'tag_info_id'   => $idTag
            ]);
            /* faq_info */
            FAQ::select('*')
                    ->where('seo_id', $idSeo)
                    ->delete();
            if(!empty($request->get('faqs'))) {
                foreach($request->get('faqs') as $faq){
                    if(!empty($faq['question'])&&!empty($faq['answer'])){
                        FAQ::insertItem([
                            'seo_id'        => $idSeo,
                            'question'      => $faq['question'],
                            'answer'        => $faq['answer'],
                        ]);   
                    }
                }   
            }
            DB::commit();
            /* Message */
            $message        = [
                'type'      => 'success',
                'message'   => '<strong>Thành công!</strong> Đã cập nhật Tag!'
            ];
            /* nếu có tùy chọn index => gửi google index */
            if(!empty($request->get('index_google'))&&$request->get('index_google')=='on') {
                $flagIndex = IndexController::indexUrl($idSeo);
                if($flagIndex==200){
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Tag và Báo Google Index!';
                }else {
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Tag! <span style="color:red;">nhưng báo Google Index lỗi</span>';
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
        return redirect()->route('admin.tag.view', ['id' => $idTag, 'language' => $language]);
    }

    public static function createOrGetTagName($idWallpaper, $table, $jsonTagName = null){
        if(!empty($idWallpaper)){
            RelationTagInfoOrther::select('*')
                ->where('reference_type', $table)
                ->where('reference_id', $idWallpaper)
                ->delete();
            $tag    = !empty($jsonTagName) ? json_decode($jsonTagName, true) : [];
            foreach($tag as $t){
                $nameTag    = strtolower($t['value']);
                /* kiểm tra xem tag name đã tồn tại chưa */
                $infoTag = Tag::select('*')
                    ->whereHas('seo', function ($query) use ($nameTag) {
                        $query->whereRaw('LOWER(title) = ?', [$nameTag]);
                    })
                    ->with('seo')
                    ->first();
                $idTag      = $infoTag->id ?? 0;
                /* chưa tồn tại -> tạo và láy ra */
                if(empty($idTag)) $idTag  = self::createSeoTmp($nameTag);
                /* insert relation */
                RelationTagInfoOrther::insertItem([
                    'tag_info_id'       => $idTag,
                    'reference_type'    => $table,
                    'reference_id'      => $idWallpaper
                ]);
            }
        }
    }

    public static function createSeoTmp($nameTag){
        $idTag      = 0;
        /* tạo bảng seo tạm */
        $slug       = config('main_'.env('APP_NAME').'.auto_fill.slug.vi').'-'.Charactor::convertStrToUrl($nameTag);
        /* lấy thông tin trang cha */
        $infoParent = Category::select('*')
                        ->whereHas('seos.infoSeo', function($query){
                            $query->where('level', 1);
                        })
                        ->first();
        $level      = $infoParent->seo->level + 1;
        $parent     = $infoParent->seo->id;
        $slugFull   = $infoParent->seo->slug_full.'/'.$slug;
        /* kiểm tra slug trùng */
        $flag       = Seo::select('*')
                        ->where('slug_full', $slugFull)
                        ->first();
        if(empty($flag)){
            $idSeo      = Seo::insertItem([
                'title'                     => $nameTag,
                'seo_title'                 => $nameTag,
                'level'                     => $level,
                'parent'                    => $parent,
                'type'                      => 'tag_info',
                'slug'                      => $slug,
                'slug_full'                 => $slugFull,
                'rating_author_name'        => 1,
                'rating_author_star'        => 5,
                'rating_aggregate_count'    => rand(100,5000),
                'rating_aggregate_star'     => '4.'.rand(5, 9),
                'created_by'                => Auth::user()->id ?? 1,
                'language'                  => 'vi'
            ]);
            /* tạo bảng tag */
            $idTag      = Tag::insertItem(['seo_id' => $idSeo]);
            /* tạo Relation */
            RelationSeoTagInfo::insertItem([
                'seo_id'        => $idSeo,
                'tag_info_id'   => $idTag
            ]);
        }
        return $idTag;
    }

    public function delete(Request $request) {
        try {
            DB::beginTransaction();
            
            $id = $request->get('id');

            if (!$id) return false;

            $info = Tag::where('id', $id)
                        ->with('seo', 'seos.infoSeo.contents')
                        ->first();

            if (!$info) return false;

            // Xoá ảnh đại diện chính
            if (!empty($info->seo->image)) {
                Upload::deleteWallpaper($info->seo->image);
            }

            // Xoá các quan hệ
            $info->categories()->delete();

            // Xoá các bản ghi liên quan trong seos
            foreach ($info->seos as $s) {
                if (!empty($s->infoSeo->image)) {
                    Upload::deleteWallpaper($s->infoSeo->image);
                }

                if (!empty($s->infoSeo->contents)) {
                    foreach ($s->infoSeo->contents as $c) {
                        $c->delete();
                    }
                }

                $s->infoSeo()->delete();
                $s->delete();
            }

            // Liên quan tới dữ liệu đã index trên Melisearch
            $engineManager = app(EngineManager::class);
            $engineManager->forgetEngines();
            // Tiếp tục với phần xóa dữ liệu
            \App\Models\Tag::withoutSyncingToSearch(function () use ($info) {
                $info->delete();
            });

            // Xoá khỏi Meilisearch (nếu index tồn tại)
            try {
                $meili = new MeilisearchClient(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
                $meili->index('tag_info')->deleteDocument($id);
            } catch (\Exception $e) {
                // Bạn có thể log lỗi hoặc bỏ qua nếu không cần xử lý tiếp
                Log::warning("Meilisearch delete failed for tag ID $id: ".$e->getMessage());
            }

            DB::commit();
            return true;
        } catch (\Exception $exception){
            DB::rollBack();
            return false;
        }
    }
}
