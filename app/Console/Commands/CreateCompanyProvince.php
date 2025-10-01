<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Seo;
use App\Models\Province;
use App\Helpers\Charactor;
use App\Helpers\CrawlHelper;
use App\Models\CompanyProvince;
use App\Models\RelationSeoCompanyProvince;

class CreateCompanyProvince extends Command {
    
    protected $signature = 'create:company_province';

    protected $description = 'Chạy job tạo trang SEO cho các trang tỉnh thành doanh nghiệp thông qua command';

    public function handle()
    {
        $provinces = Province::all();

        // Lấy thông tin parent từ bảng seo
        $infoParent = Seo::select('*')
            ->where('slug', 'danh-ba-doanh-nghiep')
            ->first();

        if (!$infoParent) {
            throw new \Exception('Không tìm thấy parent SEO với slug "danh-ba-doanh-nghiep"');
        }
        $idParent = $infoParent->id;

        foreach($provinces as $province){

            // insert seo
            $title  = $province['name'];
            $description = "Thông tin doanh nghiệp tại $title, thông tin chính thức từ cổng thông tin quốc gia";
            $seoDescription  = "Danh bạ doanh nghiệp tại ".$title.": tra cứu mã số thuế, địa chỉ trụ sở, loại hình, ngành nghề kinh doanh, tình trạng hoạt động, vốn điều lệ, người đại diện pháp luật, giám đốc, kế toán trưởng, thông tin giấy phép, nơi đăng ký, phương pháp tính thuế, thời điểm tài chính và các dữ liệu chuẩn từ Cổng thông tin quốc gia – cập nhật liên tục trên Hoptackinhdoanh.com.";
            $seoTitle  = 'Danh Bạ Doanh Nghiệp tại '.$title.' | Tra Cứu Mã Số Thuế & Thông Tin';
            $slug  = Charactor::convertStrToUrl($title);
            $insertSeo = [
                'title' => $title,
                'description' => $description,
                'level' => 2,
                'parent' => $idParent,
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'slug' => $slug,
                'slug_full' => Seo::buildFullUrl($slug, $idParent),
                'type' => 'company_province',
                'rating_author_name' => 1,
                'rating_author_star' => 5,
                'rating_aggregate_count' => rand(10, 2200),
                'rating_aggregate_star' => '4.' . rand(4, 8),
                'language' => 'vi',
            ];
            $idSeo = Seo::insertItem($insertSeo);

            if(!empty($idSeo)){
                // insert table company_province
                $idCompanyProvince = CompanyProvince::insertItem([
                    'seo_id'            => $idSeo,
                    'province_info_id'  => $province->id,
                ]);

                // Insert vào bảng quan hệ seo_company_province
                RelationSeoCompanyProvince::insertItem([
                    'seo_id' => $idSeo,
                    'company_province_id' => $idCompanyProvince,
                ]);
            }

        }
    }

}
