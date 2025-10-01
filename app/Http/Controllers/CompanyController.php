<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyIndustry;
use App\Models\CompanyTime;
use App\Models\Industry;

class CompanyController extends Controller {

    // public static function loadCompanyForPage(Request $request) {
    //     $id         = $request->get('id') ?? 0;
    //     $type       = $request->get('type') ?? '';
    //     // $page       = $request->get('page') ?? 1; // laravel ngầm lấy và sử dụng
    //     $language   = $request->get('language') ?? '';
    //     $search     = $request->get('search') ?? '';
    //     $perPage    = config("main_" . env('APP_NAME') . ".paginate.per_page"); // 20

    //     $content    = [];

    //     // kiểm tra trước khi thực hiện
    //     if(empty($id)||empty($type)||empty($language)) {
    //         return false;
    //     }

    //     // xử lý cho trang page_info -> trường hợp search MST, tên công ty, số điện thoại
    //     if($type=='page_info'&&!empty($search)){
    //         $companies  = Company::select('*')
    //                         ->whereRaw("MATCH(tax_code, name, phone) AGAINST(? IN BOOLEAN MODE)", [$search])
    //                         ->orderBy('issue_date', 'DESC')
    //                         ->paginate($perPage);
    //     }
        
    //     // xử lý cho trang company_province
    //     if($type=='company_province'){
    //         $companies  = Company::select('*')
    //                         ->where('province_code', $id)
    //                         ->orderBy('issue_date', 'DESC')
    //                         ->paginate($perPage);
    //     }

    //     // xử lý cho trang company_industry
    //     if($type=='company_industry'){
    //         $industry           = CompanyIndustry::find($id);
    //         $arrayIdIndustry    = Industry::getLevelFourChildrenByCode($industry->infoIndustry->code)->toArray();
    //         $companies = Company::select('company_info.*')
    //             ->join('relation_company_info_industry_info AS r', 'company_info.id', '=', 'r.company_info_id')
    //             ->whereIn('r.industry_code', $arrayIdIndustry)
    //             ->orderBy('company_info.issue_date', 'DESC')
    //             ->distinct()
    //             ->paginate($perPage);
    //     }

    //     // xử lý cho trang company_time 
    //     if ($type == 'company_time') {
    //         $time = CompanyTime::find($id);
    //         if ($time) {
    //             $dateStart = $time->date_start;
    //             $dateEnd   = $time->date_end;

    //             $companies = Company::select('*')
    //                 ->whereBetween('issue_date', [$dateStart, $dateEnd])
    //                 ->orderBy('issue_date', 'DESC')
    //                 ->paginate($perPage);
    //         } else {
    //             $companies = collect(); // Trả về tập rỗng nếu không tìm thấy
    //         }
    //     }

    //     // lấy nội dung xhtml ===== trả ra theo số thứ tự, không theo id vì cache
    //     $i  = 1;
    //     foreach($companies as $company){
    //         $content[$i]    = view('main.companyProvince.item', [
    //                             'company'   => $company,
    //                             'language'  => $language,
    //                         ])->render();
    //         ++$i;
    //     }

    //     // lấy paginate xhtml
    //     $urlSource = $request->headers->get('referer');
    //     // Loại bỏ tham số page nếu tồn tại
    //     if (!empty($urlSource)) {
    //         $urlSource = self::removeQueryParam($urlSource, 'page');
    //     }
    //     $paginate   = view('main.snippets.paginate', [
    //                         'data'      => $companies,
    //                         'urlSource' => $urlSource,
    //                     ])->render();
        
    //     return response()->json([
    //         'content'   => $content,
    //         'paginate'  => $paginate,
    //     ]);
    // }

    public static function loadCompanyForPage(Request $request)
    {
        $id       = $request->get('id') ?? 0;
        $type     = $request->get('type') ?? '';
        $language = $request->get('language') ?? '';
        $search   = $request->get('search') ?? '';
        $perPage  = config("main_" . env('APP_NAME') . ".paginate.per_page"); // 20

        // Kiểm tra đầu vào
        if (empty($id) || empty($type) || empty($language)) {
            return false;
        }

        $companies = collect(); // Mặc định rỗng

        /**
         * 1. Trang page_info -> Search MST, tên công ty, SĐT
         * Sử dụng FULLTEXT index
         */
        if ($type === 'page_info' && !empty($search)) {
            $companies = Company::select('*')
                ->whereRaw("MATCH(tax_code, name, phone) AGAINST(? IN BOOLEAN MODE)", [$search])
                ->orderBy('issue_date', 'DESC')
                ->paginate($perPage);
        }

        /**
         * 2. Trang company_province
         * Đã có index (province_code, issue_date)
         */
        if ($type === 'company_province') {
            $companies = Company::select('*')
                ->where('province_code', $id)
                ->orderBy('issue_date', 'DESC')
                ->paginate($perPage);
        }

        /**
         * 3. Trang company_industry
         * Tối ưu bằng cách lấy danh sách ID trước rồi query theo PK
         */
        if ($type === 'company_industry') {
            $industry = CompanyIndustry::with('infoIndustry')->find($id);

            if ($industry && $industry->infoIndustry) {
                $arrayIdIndustry = Industry::getLevelFourChildrenByCode(
                    $industry->infoIndustry->code
                )->pluck('code')->all();

                if (!empty($arrayIdIndustry)) {
                    // Lấy danh sách ID từ bảng quan hệ, dùng index (industry_code, company_info_id)
                    $companyIds = DB::table('relation_company_info_industry_info')
                        ->whereIn('industry_code', $arrayIdIndustry)
                        ->pluck('company_info_id');

                    if ($companyIds->isNotEmpty()) {
                        $companies = Company::whereIn('id', $companyIds)
                            ->orderBy('issue_date', 'DESC')
                            ->paginate($perPage);
                    }
                }
            }
        }

        /**
         * 4. Trang company_time
         * Dùng index (issue_date, id) để tránh filesort
         */
        if ($type === 'company_time') {
            $time = CompanyTime::find($id);

            if ($time) {
                $dateStart = $time->date_start;
                $dateEnd   = $time->date_end;

                $companies = Company::whereBetween('issue_date', [$dateStart, $dateEnd])
                    ->orderBy('issue_date', 'DESC')
                    ->paginate($perPage);
            }
        }

        // Nếu vẫn không có dữ liệu
        if ($companies->isEmpty()) {
            return response()->json([
                'content'   => [],
                'paginate'  => ''
            ]);
        }

        // Lấy nội dung HTML
        $content = [];
        $i = 1;
        foreach ($companies as $company) {
            $content[$i] = view('main.companyProvince.item', [
                'company'  => $company,
                'language' => $language,
            ])->render();
            $i++;
        }

        // Xử lý paginate URL
        $urlSource = $request->headers->get('referer');
        if (!empty($urlSource)) {
            $urlSource = self::removeQueryParam($urlSource, 'page');
        }

        $paginate = view('main.snippets.paginate', [
            'data'      => $companies,
            'urlSource' => $urlSource,
        ])->render();

        return response()->json([
            'content'   => $content,
            'paginate'  => $paginate,
        ]);
    }

    private static function removeQueryParam($url, $key) {
        $parsed_url = parse_url($url);
        $query = [];

        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query);
            unset($query[$key]);
        }

        $path = $parsed_url['path'] ?? '';
        $new_query = http_build_query($query);

        return $path . ($new_query ? '?' . $new_query : '');
    }
}
