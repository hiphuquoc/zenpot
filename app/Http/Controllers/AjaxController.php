<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\HelperController;
use Illuminate\Http\Request;
use App\Models\CompanyCount;
use App\Models\Company;
use App\Models\CompanyTime;
use App\Models\RegistryEmail;
use App\Models\RelationFreeWallpaperUser;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Services\BuildInsertUpdateModel;
use App\Services\RedisCacheService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Cache;

class AjaxController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public static function setViewMode(Request $request){
        $viewMode          = $request->get('view_mode');
        if(!empty($viewMode)){
            Cookie::queue('view_mode', $viewMode, 0);
        }
        return true;
    }

    public static function loadLoading(){
        $xhtml      = view('main.template.loading')->render();
        echo $xhtml;
    }

    public static function registryEmail(Request $request){
        $language               = $request->get('language');
        $idRegistryEmail        = RegistryEmail::insertItem([
            'email'     => $request->get('registry_email')
        ]);
        if(!empty($idRegistryEmail)){
            $result['type']     = 'success';
            $result['title']    = config('data_language_1.'.$language.'.registry_emali_success_title');
            $result['content']  = config('data_language_1.'.$language.'.registry_emali_success_body');
        }else {
            $result['type']     = 'error';
            $result['title']    = config('data_language_1.'.$language.'.registry_emali_error_title');
            $result['content']  = config('data_language_1.'.$language.'.registry_emali_error_body');
        }
        return json_encode($result);
    }

    public static function setMessageModal(Request $request){
        $response   = view('main.modal.contentMessageModal', [
            'title'     => $request->get('title') ?? null,
            'content'   => $request->get('content') ?? null
        ])->render();
        echo $response;
    }

    public static function checkLoginAndSetShow(Request $request){
        $xhtmlModal             = '';
        $xhtmlButton            = '';
        $xhtmlButtonMobile      = '';
        $user                   = $request->user();
        $language               = $request->get('language');
        if(!empty($user)){
            /* lấy đường dẫn trang Tải xuống của tôi */
            $tmp                = Page::select('*')
                                    ->whereHas('type', function($query){
                                        $query->where('code', 'my_download');
                                    })
                                    ->first();
            $urlMyDownload      = '';
            foreach($tmp->seos as $seo){
                if(!empty($seo->infoSeo->language)&&$seo->infoSeo->language==$language) {
                    $urlMyDownload = $seo->infoSeo->slug_full;
                    break;
                }
            }
            /* đã đăng nhập => hiển thị button thông tin tài khoản */
            $xhtmlButton        = view('main.template.buttonLogin', ['user' => $user, 'language' => $language, 'urlMyDownload' => $urlMyDownload])->render();
            $xhtmlButtonMobile  = view('main.template.buttonLoginMobile', ['user' => $user, 'language' => $language, 'urlMyDownload' => $urlMyDownload])->render();
        }else {
            /* chưa đăng nhập => hiển thị button đăng nhập + modal */
            $xhtmlButton        = view('main.template.buttonLogin', ['language' => $language])->render();
            $xhtmlModal         = view('main.template.loginCustomerModal', ['language' => $language])->render();
            $xhtmlButtonMobile  = view('main.template.buttonLoginMobile', ['language' => $language])->render();
        }
        $result['modal']            = $xhtmlModal;
        $result['button']           = $xhtmlButton;
        $result['button_mobile']    = $xhtmlButtonMobile;
        return json_encode($result);
    }

    public function setViewBy(Request $request){
        Cookie::queue('view_by', $request->get('key'), 3600);
        return true;
    }

    public function setSortBy(Request $request){
        Cookie::queue('sort_by', $request->get('key'), 3600);
        return true;
    }

    public function setFeelingFreeWallpaper(Request $request){
        $type               = $request->get('type') ?? null;
        $idFreeWallpaper    = $request->get('free_wallpaper_info_id') ?? 0;
        $response           = [];
        if(!empty($type)&&!empty($idFreeWallpaper)){
            $user   = Auth::user();
            if(!empty($user)){
                $infoRelation = RelationFreeWallpaperUser::select('*')
                    ->where('free_wallpaper_info_id', $idFreeWallpaper)
                    ->where('user_info_id', $user->id)
                    ->first();
                if(!empty($infoRelation)){
                    /* update */
                    RelationFreeWallpaperUser::updateItem($infoRelation->id, [
                        'type'  => $type
                    ]);
                }else {
                    /* insert */
                    RelationFreeWallpaperUser::insertItem([
                        'free_wallpaper_info_id'    => $idFreeWallpaper,
                        'user_info_id'  => $user->id,
                        'type'  => $type
                    ]);
                }
                $response['flag']   = true;
            }else {
                $response['flag']   = false;
                $response['empty_user']   = true;
            }
        }else {
            $response['flag'] = false;
        }
        return json_encode($response);
    }

    public function updateCountViews(Request $request){
        $idSeo = $request->get('seo_id');

        // Giả sử HelperController::getFullInfoPageByIdSeo trả về đúng model
        $infoPage = HelperController::getFullInfoPageByIdSeo($idSeo);

        if ($infoPage) {
            // Tắt tự động quản lý timestamps (nếu cần)
            $infoPage->timestamps = false;
            // CẬP NHẬT SỐ VIEW MÀ KHÔNG LÀM THAY ĐỔI updated_at
            $infoPage->updateQuietly([
                'viewed' => $infoPage->viewed + 1
            ]);
        }
    }

    public function createQRLink(Request $request)
    {
        $link = $request->get('link');

        if (empty($link)) {
            return response()->json(['error' => 'Link is required'], 400);
        }

        // Tạo QR code SVG
        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('L')     // L, M, Q, H
            ->eye('circle')
            ->color(12, 53, 106)
            ->backgroundColor(255, 255, 255)
            ->generate($link);

        // Encode SVG thành base64
        $base64Svg = base64_encode($qrSvg);

        return response()->json([
            'image' => 'data:image/svg+xml;base64,' . $base64Svg
        ]);
    }

    public function countCompany(Request $request) // đếm chung (số nhiều) -> truy xuất đếm sẵn trong CSDL
    {
        $json = $request->get('data');
        $items = json_decode($json, true);

        if (empty($items) || !is_array($items)) {
            return response()->json([]);
        }

        $counts = CompanyCount::where(function ($query) use ($items) {
            foreach ($items as $item) {
                if (!empty($item['reference_id']) && !empty($item['reference_type'])) {
                    $query->orWhere(function ($q) use ($item) {
                        $q->where('reference_id', $item['reference_id'])
                        ->where('reference_type', $item['reference_type']);
                    });
                }
            }
        })->get();

        $result = [];
        foreach ($counts as $c) {
            $key = $c->reference_id . '_' . $c->reference_type; // 👈 key khớp với id trong HTML
            $result[$key] = number_format($c->total ?? 0);
        }

        return response()->json($result);
    }

    public function countCompanyTime(Request $request) // đếm riêng (số ít) cho trang company_time
    {
        $companies          = '--';
        $id                 = $request->get('company_time_id');
        $infoCompanyTime    = CompanyTime::find($id);

        $dateStart          = $infoCompanyTime->date_start;
        $dateEnd            = $infoCompanyTime->date_end;

        $companies          = Company::select('*')
                                ->whereBetween('issue_date', [$dateStart, $dateEnd])
                                ->orderBy('issue_date', 'DESC')
                                ->count();
        return response()->json([
            'count' => $companies,
            'formatted' => number_format($companies)
        ]);
    }
}
