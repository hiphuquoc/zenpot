<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Seo;
use App\Models\Province;
use App\Helpers\Charactor;
use App\Helpers\CrawlHelper;
use App\Models\CompanyIndustry;
use App\Models\Industry;
use App\Models\RelationSeoCompanyIndustry;

class CreateCompanyIndustry extends Command {
    
    protected $signature = 'create:company_industry';

    protected $description = 'Chạy job tạo trang SEO cho các trang nghành nghề doanh nghiệp thông qua command';

    public function handle()
    {
        $industries = Industry::select("*")
                        ->where('level', 1)
                        ->get();

        // Lấy thông tin parent từ bảng seo
        $infoParent = Seo::select('*')
            ->where('slug', 'danh-ba-doanh-nghiep')
            ->first();

        if (!$infoParent) {
            throw new \Exception('Không tìm thấy parent SEO với slug "danh-ba-doanh-nghiep"');
        }
        $idParent = $infoParent->id;

        foreach($industries as $industry){

            // insert seo
            $title  = $industry->description;
            $description = "Thông tin doanh nghiệp nghành $title, thông tin chính thức từ cổng thông tin quốc gia";
            $seoDescription  = "Danh bạ doanh nghiệp đang kinh doanh trong nghành ".$title.": tra cứu mã số thuế, địa chỉ trụ sở, loại hình, ngành nghề kinh doanh, tình trạng hoạt động, vốn điều lệ, người đại diện pháp luật, giám đốc, kế toán trưởng, thông tin giấy phép, nơi đăng ký, phương pháp tính thuế, thời điểm tài chính và các dữ liệu chuẩn từ Cổng thông tin quốc gia – cập nhật liên tục trên Hoptackinhdoanh.com.";
            $seoTitle  = 'Danh Bạ Doanh Nghiệp nghành '.$title.' | Tra Cứu Mã Số Thuế & Thông Tin';
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
                'type' => 'company_industry',
                'rating_author_name' => 1,
                'rating_author_star' => 5,
                'rating_aggregate_count' => rand(10, 2200),
                'rating_aggregate_star' => '4.' . rand(4, 8),
                'language' => 'vi',
            ];
            
            $idSeo = Seo::insertItem($insertSeo);

            if(!empty($idSeo)){
                // insert table company_industry
                $idCompanyIndustry = CompanyIndustry::insertItem([
                    'seo_id'            => $idSeo,
                    'industry_info_id'  => $industry->id,
                ]);

                // Insert vào bảng quan hệ seo_company_industry
                RelationSeoCompanyIndustry::insertItem([
                    'seo_id' => $idSeo,
                    'company_industry_id' => $idCompanyIndustry,
                ]);
            }

        }
    }

}
