<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Http\Requests\BlogRequest;
use App\Helpers\Upload;
use App\Models\CategoryBlog;
use App\Models\Blog;
use App\Models\Seo;
use App\Models\RelationCategoryBlogBlogInfo;
use App\Models\RelationSeoBlogInfo;
use App\Models\Prompt;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Product;
use App\Helpers\Image;
use App\Services\BuildInsertUpdateModel;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

use Laravel\Scout\EngineManager;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public function list(Request $request){
        $params             = [];
        // Search theo tên
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        // Search theo tên
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        // paginate
        $viewPerPage        = Cookie::get('viewBlogInfo') ?? 50;
        $params['paginate'] = $viewPerPage;
        $categories         = CategoryBlog::all();
        $list               = Blog::getList($params);
        return view('admin.blog.list', compact('list', 'categories', 'params', 'viewPerPage'));
    }

    public function view(Request $request){
        $message            = $request->get('message') ?? null;
        $id                 = $request->get('id') ?? 0;
        $language           = $request->get('language') ?? null;
        // kiểm tra xem ngôn ngữ có nằm trong danh sách không
        $flagView           = false;
        foreach(config('language') as $ld){
            if($ld['key']==$language) {
                $flagView   = true;
                break;
            }
        }
        // tìm theo ngôn ngữ
        $item               = Blog::select('*')
                                ->where('id', $id)
                                ->with('seo.contents', 'seos.infoSeo.contents', 'seos.infoSeo.jobAutoTranslate')
                                ->first();
        if(empty($item)) $flagView = false;
        if($flagView==true){
            // lấy item seo theo ngôn ngữ được chọn
            $itemSeo            = [];
            if(!empty($item->seos)){
                foreach($item->seos as $s){
                    if(!empty($s->infoSeo->language)&&$s->infoSeo->language==$language) {
                        $itemSeo = $s->infoSeo;
                        break;
                    }
                }
            }
            // prompts
            $prompts            = Prompt::select('*')
                                    ->where('reference_table', 'blog_info')
                                    ->get();
            // lấy category_info dùng để search sản phẩm
            $tags               = Tag::all();
            // type
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            // trang cha
            $parents            = CategoryBlog::all();
            return view('admin.blog.view', compact('item', 'itemSeo', 'prompts', 'type', 'tags', 'language', 'parents', 'message'));
        } else {
            return redirect()->route('admin.blog.list');
        }
    }

    public function createAndUpdate(Request $request){
        try {
            DB::beginTransaction();
            // ngôn ngữ
            $idSeo              = $request->get('seo_id') ?? 0;
            $idSeoVI            = $request->get('seo_id_vi') ?? 0;
            $idBlog             = $request->get('blog_info_id');
            $language           = $request->get('language');
            $categoryType       = 'blog_info';
            $type               = $request->get('type');
            // check xem là create seo hay update seo
            $action             = !empty($idSeo)&&$type=='edit' ? 'edit' : 'create';
            // upload image
            $dataPath           = [];
            if($request->hasFile('image')) {
                $name           = !empty($request->get('slug')) ? $request->get('slug') : time();
                $fileName       = $name.'.'.config('image.extension');
                $folderUpload   =  config('main_'.env('APP_NAME').'.google_cloud_storage.images');
                $dataPath       = Upload::uploadWallpaper($request->file('image'), $fileName, $folderUpload);
            }
           // update page & content
            $seo                = $this->BuildInsertUpdateModel->buildArrayTableSeo($request->all(), $categoryType, $dataPath);
            if($action=='edit'){
                // insert seo_content => ghi chú quan trọng: vì trong update Item có tính năng replace url thay đổi trong content, nên bắt buộc phải cập nhật content trước để cố định dữ liệu
                if(!empty($request->get('content'))) CategoryController::insertAndUpdateContents($idSeo, $request->get('content'));
                // update seo
                Seo::updateItem($idSeo, $seo);
            }else {
                $idSeo = Seo::insertItem($seo, $idSeoVI);
                // insert seo_content
                if(!empty($request->get('content'))) CategoryController::insertAndUpdateContents($idSeo, $request->get('content'));
            }
            // update những phần khác
            if($language=='vi'){
                // insert hoặc update blog_info
                $status           = !empty($request->get('status'))&&$request->get('status')=='on' ? 1 : 0;
                $outstanding      = !empty($request->get('outstanding'))&&$request->get('outstanding')=='on' ? 1 : 0;
                if(empty($idBlog)){ // check xem create category hay update category
                    $idBlog          = Blog::insertItem([
                        'status'        => $status,
                        'outstanding'   => $outstanding,
                        'seo_id'        => $idSeo,
                    ]);
                }else {
                    Blog::updateItem($idBlog, [
                        'status'        => $status,
                        'outstanding'   => $outstanding,
                    ]);
                }
                // insert relation_category_blog_blog_info
                RelationCategoryBlogBlogInfo::select('*')
                    ->where('blog_info_id', $idBlog)
                    ->delete();
                if(!empty($request->get('categories'))){
                    foreach($request->get('categories') as $idCategoryBlog){
                        RelationCategoryBlogBlogInfo::insertItem([
                            'category_blog_id'  => $idCategoryBlog,
                            'blog_info_id'      => $idBlog
                        ]);
                    }
                }
            }
            // relation_seo_blog_info
            $relationSeoBlogInfo = RelationSeoBlogInfo::select('*')
                                    ->where('seo_id', $idSeo)
                                    ->where('blog_info_id', $idBlog)
                                    ->first();
            if(empty($relationSeoBlogInfo)) RelationSeoBlogInfo::insertItem([
                'seo_id'        => $idSeo,
                'blog_info_id'   => $idBlog
            ]);
            DB::commit();
            // Message
            $message        = [
                'type'      => 'success',
                'message'   => '<strong>Thành công!</strong> Đã cập nhật Bài Viết!'
            ];
            // nếu có tùy chọn index => gửi google index
            if(!empty($request->get('index_google'))&&$request->get('index_google')=='on') {
                $flagIndex = IndexController::indexUrl($idSeo);
                if($flagIndex==200){
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Bài Viết và Báo Google Index!';
                }else {
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Bài Viết <span style="color:red;">nhưng báo Google Index lỗi</span>';
                }
            }
        } catch (\Exception $exception){
            DB::rollBack();
        }
        // có lỗi mặc định Message
        if(empty($message)){
            $message        = [
                'type'      => 'danger',
                'message'   => '<strong>Thất bại!</strong> Có lỗi xảy ra, vui lòng thử lại'
            ];
        }
        $request->session()->put('message', $message);
        return redirect()->route('admin.blog.view', ['id' => $idBlog, 'language' => $language]);
    }

    public function delete(Request $request){
        try {
            DB::beginTransaction();
            
            $id = $request->get('id');

            if (!$id) return false;

            $info       = Blog::select('*')
                            ->where('id', $id)
                            ->with('seo', 'seos')
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
            \App\Models\Blog::withoutSyncingToSearch(function () use ($info) {
                $info->delete();
            });

            // Xoá khỏi Meilisearch (nếu index tồn tại)
            try {
                $meili = new MeilisearchClient(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
                $meili->index('blog_info')->deleteDocument($id);
            } catch (\Exception $e) {
                // Bạn có thể log lỗi hoặc bỏ qua nếu không cần xử lý tiếp
                Log::warning("Meilisearch delete failed for blog ID $id: ".$e->getMessage());
            }
            DB::commit();
            return true;
        } catch (\Exception $exception){
            DB::rollBack();
            return false;
        }
    }
}
