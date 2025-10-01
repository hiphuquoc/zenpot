<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Upload;
use App\Models\Seo;
use App\Models\Exchange;
use App\Models\Prompt;
use App\Models\RelationSeoExchangeTag;
use App\Models\RelationExchangeInfoExchangeTag;
use App\Models\ExchangeTag;
use App\Services\BuildInsertUpdateModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

class ExchangeTagController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public static function list(Request $request){
        $params                         = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* paginate */
        $viewPerPage        = Cookie::get('viewExchangeTag') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = ExchangeTag::getList($params);
        return view('admin.exchangeTag.list', compact('list', 'viewPerPage', 'params'));
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
        $item               = ExchangeTag::select('*')
                                ->where('id', $id)
                                ->with('seo.contents', 'seos.infoSeo.contents', 'exchanges')
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
                                    ->where('reference_table', 'exchange_tag')
                                    ->get();
            /* exchanges */
            $exchanges          = Exchange::all();
            /* trang cha */
            $parents            = Exchange::all();
            /* type */
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            return view('admin.exchangeTag.view', compact('item', 'itemSeo', 'prompts', 'type', 'exchanges', 'parents', 'language', 'message'));
        } else {
            return redirect()->route('admin.exchangeTag.list');
        }
    }

    public function createAndUpdate(Request $request){
        try {
            DB::beginTransaction();
            /* ngôn ngữ */
            $idSeo              = $request->get('seo_id');
            $idSeoVI            = $request->get('seo_id_vi') ?? 0;
            $idExchangeTag         = $request->get('exchange_tag_id');
            $language           = $request->get('language');
            $categoryType       = 'exchange_tag';
            $type               = $request->get('type');
            $sign               = $request->get('sign') ?? null;
            $icon               = $request->get('icon') ?? null;
            $typeFilter         = $request->get('filter') ?? null;
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
            /* update page */
            $seo                = $this->BuildInsertUpdateModel->buildArrayTableSeo($request->all(), $categoryType, $dataPath);
            if($action=='edit'){
                Seo::updateItem($idSeo, $seo);
            }else {
                $idSeo = Seo::insertItem($seo, $idSeoVI);
            }
            /* kiểm tra insert thành công không */
            if(!empty($idSeo)){
                /* insert seo_content */
                if(!empty($request->get('content'))) CategoryController::insertAndUpdateContents($idSeo, $request->get('content'));
                if($language=='vi'){
                    /* insert hoặc update category_info */
                    $flagShow           = !empty($request->get('flag_show'))&&$request->get('flag_show')=='on' ? 1 : 0;
                    if(empty($idExchangeTag)){ /* check xem create category hay update category */
                        $idExchangeTag          = ExchangeTag::insertItem([
                            'flag_show'     => $flagShow,
                            'seo_id'        => $idSeo,
                            'sign'          => $sign,
                            'icon'          => $icon,
                            'type_filter'   => $typeFilter,
                        ]);
                    }else {
                        ExchangeTag::updateItem($idExchangeTag, [
                            'flag_show'     => $flagShow,
                            'sign'          => $sign,
                            'icon'          => $icon,
                            'type_filter'   => $typeFilter,
                        ]);
                    }
                    /* insert relation_exchange_info_exchange_tag */
                    RelationExchangeInfoExchangeTag::select('*')
                        ->where('exchange_tag_id', $idExchangeTag)
                        ->delete();
                    if(!empty($request->get('exchanges'))){
                        foreach($request->get('exchanges') as $idExchange){
                            RelationExchangeInfoExchangeTag::insertItem([
                                'exchange_info_id'  => $idExchange,
                                'exchange_tag_id'       => $idExchangeTag
                            ]);
                        }
                    }
                }
                /* relation_seo_category_info */
                $relationSeoCategoryInfo = RelationSeoExchangeTag::select('*')
                                        ->where('seo_id', $idSeo)
                                        ->where('exchange_tag_id', $idExchangeTag)
                                        ->first();
                if(empty($relationSeoCategoryInfo)) RelationSeoExchangeTag::insertItem([
                    'seo_id'        => $idSeo,
                    'exchange_tag_id'   => $idExchangeTag
                ]);
                DB::commit();
                /* Message */
                $message        = [
                    'type'      => 'success',
                    'message'   => '<strong>Thành công!</strong> Đã cập nhật Category Blog!'
                ];
                /* nếu có tùy chọn index => gửi google index */
                if(!empty($request->get('index_google'))&&$request->get('index_google')=='on') {
                    $flagIndex = IndexController::indexUrl($idSeo);
                    if($flagIndex==200){
                        $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Category Blog và Báo Google Index!';
                    }else {
                        $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Category Blog <span style="color:red;">nhưng báo Google Index lỗi</span>';
                    }
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
        return redirect()->route('admin.exchangeTag.view', ['id' => $idExchangeTag, 'language' => $language]);
    }

    public function delete(Request $request){
        try {
            DB::beginTransaction();
            
            $id = $request->get('id');

            if (!$id) return false;

            $info       = ExchangeTag::select('*')
                            ->where('id', $id)
                            ->with('seo', 'seos')
                            ->first();

            // Xoá ảnh đại diện chính
            if (!empty($info->seo->image)) {
                Upload::deleteWallpaper($info->seo->image);
            }

            // Xoá các quan hệ
            $info->exchanges()->delete();

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
            
            $info->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $exception){
            DB::rollBack();
            return false;
        }
    }
}