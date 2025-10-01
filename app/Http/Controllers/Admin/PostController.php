<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Helpers\Upload;
use App\Models\Exchange;
use App\Models\ExchangeTag;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\Seo;
use App\Models\RelationExchangeInfoPostInfo;
use App\Models\RelationExchangeTagPostInfo;
use App\Models\RelationSeoPostInfo;
use App\Models\Prompt;
use App\Models\PostContent;
use App\Models\PostContact;
use App\Models\RelationPostInfoExchangeOutstanding;
// use App\Models\Product;
// use App\Helpers\Image;
use App\Services\BuildInsertUpdateModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;

use Laravel\Scout\EngineManager;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Http\Request;

class PostController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public function list(Request $request){
        $params             = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* Search theo tên */
        if(!empty($request->get('search_category'))) $params['search_category'] = $request->get('search_category');
        /* paginate */
        $viewPerPage        = Cookie::get('viewPostInfo') ?? 50;
        $params['paginate'] = $viewPerPage;
        $exchangeInfos      = Exchange::all();
        $exchangeTags       = ExchangeTag::all();
        $list               = Post::getList($params);
        return view('admin.post.list', compact('list', 'params', 'exchangeInfos', 'exchangeTags', 'viewPerPage'));
    }

    public function view(Request $request){
        $message            = $request->get('message') ?? null;
        $id                 = $request->get('id') ?? 0;
        $language           = $request->get('language') ?? null;
        /* kiểm tra xem ngôn ngữ có nằm trong danh sách không */
        $flagView           = false;
        foreach(config('language') as $ld){
            if($ld['key']==$language) {
                $flagView   = true;
                break;
            }
        }
        /* tìm theo ngôn ngữ */
        $item               = Post::select('*')
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
                                    ->where('reference_table', 'post_info')
                                    ->get();
            /* type */
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            /* trang cha */
            $parents            = Exchange::all();
            /* exchange tags */
            $exchangeTags       = ExchangeTag::all();
            return view('admin.post.view', compact('item', 'itemSeo', 'prompts', 'type', 'language', 'parents', 'exchangeTags', 'message'));
        } else {
            return redirect()->route('admin.post.list');
        }
    }

    public function createAndUpdate(PostRequest $request) {
        try {
            DB::beginTransaction();
            Log::info('Bắt đầu createAndUpdate', ['request_data' => $request->all()]);

            /* ngôn ngữ */
            $idSeo = $request->get('seo_id') ?? 0;
            $idSeoVI = $request->get('seo_id_vi') ?? 0;
            $idPost = $request->get('post_info_id');
            $language = $request->get('language');
            $tableType = 'post_info';
            $type = $request->get('type');
            $action = !empty($idSeo) && $type == 'edit' ? 'edit' : 'create';
            Log::info('Thông tin cơ bản', [
                'idSeo' => $idSeo,
                'idSeoVI' => $idSeoVI,
                'idPost' => $idPost,
                'language' => $language,
                'action' => $action
            ]);

            /* upload image */
            $dataPath = [];
            if ($request->hasFile('image')) {
                Log::info('Bắt đầu upload ảnh');
                $name = !empty($request->get('slug')) ? $request->get('slug') : time();
                $fileName = $name . '.' . config('image.extension');
                $folderUpload = config('main_' . env('APP_NAME') . '.google_cloud_storage.images');
                $dataPath = Upload::uploadWallpaper($request->file('image'), $fileName, $folderUpload);
                Log::info('Upload ảnh thành công', ['dataPath' => $dataPath]);
            }

            /* update page & content */
            Log::info('Xây dựng dữ liệu SEO');
            $seo = $this->BuildInsertUpdateModel->buildArrayTableSeo($request->all(), $tableType, $dataPath);
            if ($action == 'edit') {
                Log::info('Cập nhật SEO', ['idSeo' => $idSeo]);
                if (!empty($request->get('contents'))) {
                    Log::info('Cập nhật nội dung SEO', ['contents' => $request->get('contents')]);
                    self::insertAndUpdateContents($idSeo, $request->get('contents'));
                }
                Seo::updateItem($idSeo, $seo);
            } else {
                Log::info('Tạo SEO mới');
                $idSeo = Seo::insertItem($seo, $idSeoVI);
                if (!empty($request->get('contents'))) {
                    Log::info('Tạo nội dung SEO', ['contents' => $request->get('contents')]);
                    self::insertAndUpdateContents($idSeo, $request->get('contents'));
                }
            }

            /* update những phần khác */
            if ($language == 'vi') {
                /* insert hoặc update post_info */
                $outstanding = !empty($request->get('outstanding')) && $request->get('outstanding') == 'on' ? 1 : 0;
                $typeVip = $request->get('type_vip') ?? 0;
                $ribbon = $request->get('ribbon') ?? 0;
                $status = $request->get('status') ?? 0;
                Log::info('Xử lý post_info', [
                    'outstanding' => $outstanding,
                    'typeVip' => $typeVip,
                    'ribbon' => $ribbon,
                    'status' => $status
                ]);

                if (empty($idPost)) {
                    Log::info('Tạo bài đăng mới');
                    $data = [
                        'seo_id' => $idSeo,
                        'type_vip' => $typeVip,
                        'ribbon' => $ribbon,
                        'status' => $status,
                        'outstanding' => $outstanding,
                    ];
                    $idPost = Post::insertItem($data);
                    Log::info('Tạo bài đăng thành công', ['idPost' => $idPost]);
                } else {
                    Log::info('Cập nhật bài đăng', ['idPost' => $idPost]);
                    $data = [
                        'type_vip' => $typeVip,
                        'ribbon' => $ribbon,
                        'status' => $status,
                        'outstanding' => $outstanding,
                    ];
                    Post::updateItem($idPost, $data);
                }

                /* Update or Insert post_contact */
                if (!empty($request->get('contact_phone'))) {
                    Log::info('Xử lý thông tin liên hệ', ['post_info_id' => $idPost]);
                    $postContact = PostContact::firstOrNew(['post_info_id' => $idPost]);
                    if ($request->hasFile('contact_avatar')) {
                        Log::info('Bắt đầu upload avatar liên hệ');
                        $name = 'avatar-' . \App\Helpers\Charactor::randomString(10);
                        $fileName = $name . '.' . config('image.extension');
                        $folderUpload = config('main_' . env('APP_NAME') . '.google_cloud_storage.images');
                        $urlAvatar = Upload::uploadAvatar($request->file('contact_avatar'), $fileName, $folderUpload);
                        $postContact->avatar_file_cloud = $urlAvatar;
                        Log::info('Upload avatar liên hệ thành công', ['urlAvatar' => $urlAvatar]);
                    }
                    $postContact->name = $request->get('contact_name') ?? 'Người đăng';
                    $postContact->position = $request->get('contact_position');
                    $postContact->phone = $request->get('contact_phone');
                    $postContact->zalo = $request->get('contact_zalo') ?? null;
                    $postContact->email = $request->get('contact_email') ?? null;
                    $postContact->save();
                    Log::info('Lưu thông tin liên hệ thành công');
                }

                /* insert gallery và lưu CSDL */
                if ($request->hasFile('galleries')) {
                    Log::info('Bắt đầu upload thư viện ảnh');
                    $params = [
                        'attachment_id' => $idPost,
                        'relation_table' => $tableType,
                        'name' => $request->get('slug'),
                        'file_type' => 'gallery',
                    ];
                    GalleryController::upload($request->file('galleries'), $params);
                    Log::info('Upload thư viện ảnh thành công');
                }

                /* insert relation_exchange_info_post_info */
                Log::info('Xóa quan hệ exchange_info cũ', ['post_info_id' => $idPost]);
                RelationExchangeInfoPostInfo::select('*')
                    ->where('post_info_id', $idPost)
                    ->delete();
                if (!empty($request->get('exchanges'))) {
                    Log::info('Thêm quan hệ exchange_info mới', ['exchanges' => $request->get('exchanges')]);
                    foreach ($request->get('exchanges') as $idExchangeInfo) {
                        RelationExchangeInfoPostInfo::insertItem([
                            'exchange_info_id' => $idExchangeInfo,
                            'post_info_id' => $idPost
                        ]);
                    }
                }

                /* insert relation_exchange_tag_post_info */
                Log::info('Xóa quan hệ exchange_tag cũ', ['post_info_id' => $idPost]);
                RelationExchangeTagPostInfo::select('*')
                    ->where('post_info_id', $idPost)
                    ->delete();
                if (!empty($request->get('exchangeTags'))) {
                    Log::info('Thêm quan hệ exchange_tag mới', ['exchangeTags' => $request->get('exchangeTags')]);
                    foreach ($request->get('exchangeTags') as $idExchangeTag) {
                        RelationExchangeTagPostInfo::insertItem([
                            'exchange_tag_id' => $idExchangeTag,
                            'post_info_id' => $idPost
                        ]);
                    }
                }

                /* insert relation_post_info_exchange_outstanding */
                Log::info('Xóa quan hệ exchange_outstanding cũ', ['post_info_id' => $idPost]);
                RelationPostInfoExchangeOutstanding::select('*')
                    ->where('post_info_id', $idPost)
                    ->delete();
                if (!empty($request->get('exchangeOutstandings'))) {
                    Log::info('Thêm quan hệ exchange_outstanding mới', ['exchangeOutstandings' => $request->get('exchangeOutstandings')]);
                    foreach ($request->get('exchangeOutstandings') as $idExchangeOutstanding) {
                        RelationPostInfoExchangeOutstanding::insertItem([
                            'exchange_outstanding_id' => $idExchangeOutstanding,
                            'post_info_id' => $idPost,
                        ]);
                    }
                }
            }

            /* relation_seo_post_info */
            Log::info('Kiểm tra quan hệ seo_post_info', ['seo_id' => $idSeo, 'post_info_id' => $idPost]);
            $relationSeoPostInfo = RelationSeoPostInfo::select('*')
                ->where('seo_id', $idSeo)
                ->where('post_info_id', $idPost)
                ->first();
            if (empty($relationSeoPostInfo)) {
                RelationSeoPostInfo::insertItem([
                    'seo_id' => $idSeo,
                    'post_info_id' => $idPost
                ]);
                Log::info('Thêm quan hệ seo_post_info mới');
            }

            DB::commit();
            Log::info('Hoàn thành createAndUpdate', ['idPost' => $idPost]);

            /* Message */
            $message = [
                'type' => 'success',
                'message' => '<strong>Thành công!</strong> Đã cập nhật Bài Đăng!'
            ];
            /* nếu có tùy chọn index => gửi google index */
            if (!empty($request->get('index_google')) && $request->get('index_google') == 'on') {
                Log::info('Gửi yêu cầu Google Index', ['idSeo' => $idSeo]);
                $flagIndex = IndexController::indexUrl($idSeo);
                if ($flagIndex == 200) {
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Bài Đăng và Báo Google Index!';
                } else {
                    $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Bài Đăng <span style="color:red;">nhưng báo Google Index lỗi</span>';
                }
                Log::info('Kết quả Google Index', ['flagIndex' => $flagIndex]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Lỗi trong createAndUpdate', [
                'error_message' => $exception->getMessage(),
                'stack_trace' => $exception->getTraceAsString(),
                'request_data' => $request->all(),
                'idSeo' => $idSeo ?? null,
                'idPost' => $idPost ?? null
            ]);
        }

        // trường hợp gọi từ giả lập => crawl
        if ($request->get('crawl')) {
            Log::info('Trả về idPost cho crawl', ['idPost' => $idPost]);
            return $idPost;
        }

        /* có lỗi mặc định Message */
        if (empty($message)) {
            $message = [
                'type' => 'danger',
                'message' => '<strong>Thất bại!</strong> Có lỗi xảy ra, vui lòng thử lại'
            ];
            Log::warning('Không có message, gán message lỗi mặc định', ['message' => $message]);
        }

        $request->session()->put('message', $message);
        Log::info('Chuyển hướng về admin.post.view', ['idPost' => $idPost, 'language' => $language]);
        return redirect()->route('admin.post.view', ['id' => $idPost, 'language' => $language]);
    }

    public function delete(Request $request){
        try {
            DB::beginTransaction();
            
            $id = $request->get('id');

            if (!$id) return false;

            $info       = Post::select('*')
                            ->where('id', $id)
                            ->with('seo', 'seos')
                            ->first();

            if (!$info) return false;

            // Xoá ảnh đại diện chính
            if (!empty($info->seo->image)) {
                Upload::deleteWallpaper($info->seo->image);
            }

            // Xoá các quan hệ
            $info->contact()->delete();
            $info->exchanges()->delete();
            $info->exchangeTags()->delete();
            $info->exchangeOutstandings()->delete();
            foreach($info->files as $file){
                GalleryController::removeById($file->id);
            }
            $info->files()->delete();

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
            \App\Models\Post::withoutSyncingToSearch(function () use ($info) {
                $info->delete();
            });

            // Xoá khỏi Meilisearch (nếu index tồn tại)
            try {
                $meili = new MeilisearchClient(env('MEILISEARCH_HOST'), env('MEILISEARCH_KEY'));
                $meili->index('post_info')->deleteDocument($id);
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

    private static function insertAndUpdateContents($idSeo, $arrayContent){
        PostContent::select('*')
            ->where('seo_id', $idSeo)
            ->delete();
        foreach($arrayContent as $content){
            if(!empty($content['content_title'])&&!empty($content['content']))
            PostContent::insertItem([
                'seo_id'    => $idSeo,
                'title'     => $content['content_title'],
                'sub_title' => $content['content_sub_title'],
                'icon'      => $content['content_icon'],
                'ordering'  => $content['content_ordering'],
                'content'   => $content['content'],
            ]);
        }
    }

    public static function removeSystemFileById(Request $request){
        $idSystemFile = $request->get('system_file_id') ?? 0;
        if(!empty($idSystemFile)){

        }
        return false;
    }

    public static function uploadAttachment(Request $request) {
        // Validate đầu vào
        $request->validate([
            'attachment_title' => 'required|string|max:255',
            'attachment_file'  => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400', // 100MB
            'seo_id'           => 'nullable|integer|exists:seo,id',
        ]);

        $file = $request->file('attachment_file');

        // Lấy tên file chuẩn SEO
        $fileName = \App\Helpers\Charactor::convertStrToUrl($request->get('attachment_title')) . '-' . time();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getClientMimeType();

        // Lấy folder upload từ config
        $folderUpload = config('main_' . env('APP_NAME') . '.google_cloud_storage.files');

        // Upload file
        $filePath = Upload::uploadFile($file, $fileName, $folderUpload);

        // Lưu dữ liệu vào DB
        $id = PostAttachment::insertItem([
            'seo_id'         => $request->get('seo_id'),
            'title'          => $request->get('attachment_title'),
            'file_name'      => $file->getClientOriginalName(),
            'file_extension' => $extension,
            'file_type'      => $mimeType,
            'file_cloud'     => $filePath,
        ]);

        // Lấy lại thông tin SEO để render view
        $infoAttachment = PostAttachment::find($id);

        // Render đoạn HTML hiển thị file đã upload
        $xhtml = view('admin.post.rowAttachment', compact('infoAttachment'))->render();

        return $xhtml;
    }

    
    public static function deleteAttachment(Request $request) {
        $request->validate([
            'id' => 'required|integer|exists:post_attachment,id',
        ]);

        $attachment = PostAttachment::find($request->id);

        if (!$attachment) {
            abort(404, 'Tài liệu không tồn tại.');
        }

        // Xoá file trên GCS nếu tồn tại
        $gcsDisk = Storage::disk('gcs');
        if ($gcsDisk->exists($attachment->file_cloud)) {
            $gcsDisk->delete($attachment->file_cloud);
        }

        // Xoá bản ghi trong cơ sở dữ liệu
        $attachment->delete();

        return response()->json(['success' => true]);
    }
}
