<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\CompanyProvince;
use App\Models\Prompt;
use App\Models\Seo;
use App\Models\Page;
use App\Models\RelationSeoCompanyInfo;
use App\Helpers\Charactor;
use App\Http\Requests\CompanyRequest;
use App\Helpers\Upload;
use App\Models\CompanyService;
use App\Services\BuildInsertUpdateModel;

class CompanyController extends Controller {

    public function __construct(BuildInsertUpdateModel $BuildInsertUpdateModel){
        $this->BuildInsertUpdateModel  = $BuildInsertUpdateModel;
    }

    public static function list(Request $request){
        $params             = [];
        /* Search theo tên */
        if(!empty($request->get('search_name'))) $params['search_name'] = $request->get('search_name');
        /* paginate */
        $viewPerPage        = Cookie::get('viewCompanyInfo') ?? 20;
        $params['paginate'] = $viewPerPage;
        $list               = Company::getList($params);
        return view('admin.company.list', compact('list', 'params', 'viewPerPage'));
    }

    public function view(Request $request){
        $message            = $request->get('message') ?? null;
        $id                 = $request->get('id') ?? 0;
        $language           = $request->get('language') ?? 'vi';
        /* kiểm tra xem ngôn ngữ có nằm trong danh sách không */
        $flagView           = false;
        foreach(config('language') as $ld){
            if($ld['key']==$language) {
                $flagView   = true;
                break;
            }
        }
        /* tìm theo ngôn ngữ */
        $item               = Company::select('*')
                                ->where('id', $id)
                                ->with('seo.contents', 'seos.infoSeo.contents')
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
                                    ->where('reference_table', 'exchange_info')
                                    ->get();
            /* trang cha */
            $parents            = Page::all();
            /* type */
            $type               = !empty($itemSeo) ? 'edit' : 'create';
            $type               = $request->get('type') ?? $type;
            return view('admin.company.view', compact('item', 'itemSeo', 'prompts', 'parents', 'type', 'language', 'message'));
        } else {
            return redirect()->route('admin.company.list');
        }
    }

    public function createAndUpdate(CompanyRequest $request){
        // try {
        //     DB::beginTransaction();           
            /* ngôn ngữ */
            $keyTable           = 'company_info';
            $idSeo              = $request->get('seo_id') ?? 0;
            $idSeoVI            = $request->get('seo_id_vi') ?? 0;
            $idCompany             = $request->get('company_info_id');
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
                /* update seo */
                Seo::updateItem($idSeo, $seo);
            }else {
                $idSeo = Seo::insertItem($seo, $idSeoVI);
            }
            /* update những phần khác */
            if($language=='vi'){
                /* update ===== luôn là update vì company_info không thể tự tạo
                    mô tả vip   - subtitle_vip
                    logo        - file_cloud_logo 
                    website     - website
                */
                $insertCompany = [
                    'subtitle_vip'  => $request->get('subtitle_vip'),
                    'website'       => $request->get('website'),
                ];

                // trường hợp có cập nhật nhật logo
                if($request->hasFile('file_cloud_logo')){
                    $name           = !empty($request->get('slug')) ? 'logo-'.$request->get('slug') : 'logo-'.time();
                    $fileName       = $name.'.'.config('image.extension');
                    $folderUpload   =  config('main_'.env('APP_NAME').'.google_cloud_storage.images');
                    $dataPath       = Upload::uploadWallpaper($request->file('file_cloud_logo'), $fileName, $folderUpload);
                    $insertCompany['file_cloud_logo'] = $dataPath;
                }

                if(!empty($idCompany)) Company::updateItem($idCompany, $insertCompany);
            }

            // cập nhật/thêm dịch vụ
            foreach($request->get('repeater_company_service') as $keyIndex => $companyService){
                if(!empty($companyService['title'])){
                    $dataService = [
                        'company_info_id'   => $idCompany,
                        'title' => $companyService['title'],
                        'description'   => $companyService['description'],
                        'url'   => $companyService['url'] ?? null,
                    ];
                    if(!empty($request->hasFile('file_cloud_logo')[$keyIndex])&&$request->hasFile('file_cloud_logo')[$keyIndex]){
                        $name           = !empty($request->get('slug')) ? 'company_service_'.$keyIndex.'-'.$request->get('slug') : 'company_service_'.$keyIndex.'-'.time();
                        $fileName       = $name.'.'.config('image.extension');
                        $folderUpload   =  config('main_'.env('APP_NAME').'.google_cloud_storage.images');
                        $dataPath       = Upload::uploadWallpaper($request->hasFile('file_cloud_logo')[$keyIndex], $fileName, $folderUpload);
                        $dataService['image'] = $dataPath;
                    }

                    if(!empty($companyService['id'])){
                        // update
                        CompanyService::updateItem($companyService['id'], $dataService);
                    }else {
                        // insert
                        CompanyService::insertItem($dataService);
                    }
                }
            }
            // xóa những dịch vụ không còn trong mảng
            $services   = CompanyService::select('*')
                            ->where('company_info_id', $idCompany)
                            ->get();
            foreach($services as $service){
                $idService  = $service->id;
                $flagDelete = true;
                foreach($request->get('repeater_company_service') as $s){
                    if($idService==$s['id']) {
                        $flagDelete = false;
                        break;
                    }
                }
                if($flagDelete) $service->delete();
            }

            /* relation_seo_company_info */
            $relationSeoCompanyInfo = RelationSeoCompanyInfo::select('*')
                                    ->where('seo_id', $idSeo)
                                    ->where('company_info_id', $idCompany)
                                    ->first();
            if(empty($relationSeoCompanyInfo)) RelationSeoCompanyInfo::insertItem([
                'seo_id'        => $idSeo,
                'company_info_id'   => $idCompany
            ]);
            
        //     DB::commit();
        //     /* Message */
        //     $message        = [
        //         'type'      => 'success',
        //         'message'   => '<strong>Thành công!</strong> Đã cập nhật Doanh nghiệp!'
        //     ];
        //     /* nếu có tùy chọn index => gửi google index */
        //     if(!empty($request->get('index_google'))&&$request->get('index_google')=='on') {
        //         $flagIndex = IndexController::indexUrl($idSeo);
        //         if($flagIndex==200){
        //             $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Doanh nghiệp và Báo Google Index!';
        //         }else {
        //             $message['message'] = '<strong>Thành công!</strong> Đã cập nhật Doanh nghiệp! <span style="color:red;">nhưng báo Google Index lỗi</span>';
        //         }
        //     }
        // } catch (\Exception $exception){
        //     DB::rollBack();
        // }
        // /* có lỗi mặc định Message */
        // if(empty($message)){
        //     $message        = [
        //         'type'      => 'danger',
        //         'message'   => '<strong>Thất bại!</strong> Có lỗi xảy ra, vui lòng thử lại'
        //     ];
        // }
        // $request->session()->put('message', $message);
        // return redirect()->route('admin.company.view', ['id' => $idCompany, 'language' => $language]);
    }

    /**
     * đồng bộ các tên tỉnh thành từ nguồn bên ngoài vói CSDL
     *
     * @param string $taxAddress
     * @return array 
     */
    public static function determineProvince(string $taxAddress){
        // lấy tất cả thông tin tỉnh thành
        $provinces = CompanyProvince::select('*')
                        ->with('infoProvince')
                        ->get();

        // tạo array map từ provinces
        $arrayMap = [];
        $i = 0;

        foreach($provinces as $province){
            if(!empty($province->infoProvince->merged_from)){
                $tmp = explode(',', mb_strtolower($province->infoProvince->merged_from));
                foreach($tmp as $t){
                    $cleanString = Charactor::cleanString(trim($t));
                    $cleanString = Charactor::convertStrToUrl($cleanString, ' ');
                    $arrayMap[$i]['id'] = $province->id;
                    $arrayMap[$i]['name_true'] = mb_strtolower($province->infoProvince->name);
                    $arrayMap[$i]['name'] = $cleanString; // chỉ 1 chuỗi duy nhất
                    $i++;
                }
            } else {
                $cleanString = Charactor::cleanString(mb_strtolower($province->infoProvince->name));
                $cleanString = Charactor::convertStrToUrl($cleanString, ' ');
                $arrayMap[$i]['id'] = $province->id;
                $arrayMap[$i]['name_true'] = mb_strtolower($province->infoProvince->name);
                $arrayMap[$i]['name'] = $cleanString; // chỉ 1 chuỗi duy nhất
                $i++;
            }
        }

        // Chuẩn hóa địa chỉ
        $tmp = mb_strtolower($taxAddress);
        $tmp = explode(',', $tmp);
        if(trim(end($tmp)) == 'việt nam'){
            array_pop($tmp);
        }

        $provinceText = Charactor::cleanString(trim(end($tmp)));
        $provinceText = Charactor::convertStrToUrl($provinceText, ' ');

        // So sánh phần cuối cùng của $provinceText với từng $name
        $response = [];
        foreach($arrayMap as $item){
            $name = $item['name'];
            $length = mb_strlen($name, 'UTF-8');
            $slice = mb_substr($provinceText, -$length, null, 'UTF-8');

            if($slice === $name){
                $response['province_code'] = $item['id'];
                $response['province_name'] = $item['name_true'];
                break;
            }
        }

        return $response;
    }

}
