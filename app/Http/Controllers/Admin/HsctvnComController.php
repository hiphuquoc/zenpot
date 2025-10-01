<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ChatGptController;
use Intervention\Image\ImageManagerStatic;
use App\Helpers\Charactor;
use App\Helpers\Upload;
use App\Helpers\Url;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Seo;
use App\Models\Post;
use App\Models\Exchange;
use App\Models\ExchangeTag;
use App\Models\Crawl;
use App\Models\SystemFile;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Helpers\CrawlHelper;
use App\Models\RelationSeoCompanyInfo;
use App\Models\Company;
use App\Models\RelationCompanyInfoIndustryInfo;
use Carbon\Carbon;

use App\Jobs\CrawlDetailCompanyMulti;

class HsctvnComController extends Controller {

    public static function handleCrawlList($pageStart, $pageEnd = '') {
        if (empty($pageEnd)) {
            $pageEnd = $pageStart;
        }

        // Xác định lại thứ tự để crawl từ trang lớn đến trang nhỏ
        $start = max($pageStart, $pageEnd);
        $end = min($pageStart, $pageEnd);

        for ($i = $start; $i >= $end; --$i) {
            $urlList = "https://hsctvn.com/page-{$i}";

            $urls = HsctvnComController::crawlList($urlList);

            // Nếu rỗng thì thử lại 1 lần nữa
            if (empty($urls) || !is_array($urls)) {
                // Delay nhẹ để tránh spam server
                sleep(1); 
                $urls = HsctvnComController::crawlList($urlList);
            }

            if (!empty($urls) && is_array($urls)) {
                $urlsCrawl = [];
                $k = 1;
                foreach ($urls as $url) {
                    $urlsCrawl[] = $url;
                    if ($k % 4 == 0 || $k == count($urls)) {
                        CrawlDetailCompanyMulti::dispatch($urlsCrawl);
                        echo "Đã yêu cầu tải " . count($urlsCrawl) . " doanh nghiệp của trang {$urlList}\n(đang chờ tải...)\n";
                        $urlsCrawl = [];
                    }
                    ++$k;
                }
            } else {
                echo "Không tìm thấy doanh nghiệp nào ở trang {$i}\n";
            }
            sleep(1);
        }
    }


    public static function crawlList($urlList) {
        $response   = [];

        if(!$urlList) return [];

        // hàm lấy html - xử lý nhiều công cụ
        $html       = CrawlHelper::getHtml($urlList);

        if (!$html) {
            return "Lỗi: Không thể lấy HTML từ URL.";
        }

        // Dùng DomCrawler để parse nội dung
        $crawler = new Crawler($html);

         // Lấy danh sách url
        $crawler->filter('ul.hsdn li h3 a')->each(function (Crawler $node) use (&$response) {
            $response[] = 'https://hsctvn.com/' . $node->attr('href');
        });

        return $response;
    }

    public static function getDetail($url, $html)
    {
        // Parse bằng DomCrawler
        $crawler = new Crawler($html);

        // Lấy tiêu đề h1
        $title = '';
        if ($crawler->filter('h1')->count() > 0) {
            $title = trim($crawler->filter('h1')->first()->text());
        }

        // Khởi tạo response
        $response = [
            'name'                      => $title,
            'international_name'        => '',
            'short_name'                => '',
            'tax_code'                  => '',
            'tax_address'               => '',
            'legal_representative'      => '',
            'phone'                     => '',
            'email'                     => '',
            'website'                   => '',
            'province_code'             => '',
            'province_name'             => '',
            'issue_date'                => '',
            'main_industry_code'        => '',
            'main_industry_text'        => '',
            'status'                    => '',
            'last_updated'              => '',
            'url_crawl'                 => $url,
            'company_industries'        => [],
        ];

        // Lấy thông tin từ các thẻ li trong ul.hsct
        $crawler->filter('ul.hsct li')->each(function (Crawler $node) use (&$response) {
            $icon = $node->filter('i.fa')->count() > 0 ? $node->filter('i.fa')->attr('class') : '';
            $text = trim($node->text());

            if (str_contains($text, '☷ Tên quốc tế:')) {
                $response['international_name'] = trim(str_replace('☷ Tên quốc tế:', '', $text));
            } elseif (str_contains($text, '☷ Tên viết tắt:')) {
                $response['short_name'] = trim(str_replace('☷ Tên viết tắt:', '', $text));
            } elseif (str_contains($icon, 'fa-hashtag')) {
                $response['tax_code'] = trim(str_replace('Mã số thuế:', '', $text));
            } elseif (str_contains($icon, 'fa-map-marker')) {
                $response['tax_address'] = trim(str_replace('Địa chỉ thuế:', '', $text));
            } elseif (str_contains($icon, 'fa-user-o')) {
                $response['legal_representative'] = trim(str_replace('Đại diện pháp luật:', '', $node->filter('a')->count() > 0 ? $node->filter('a')->text() : $text));
            } elseif (str_contains($icon, 'fa-phone')) {
                $response['phone'] = str_replace(['Điện thoại:', ' ', '.'], '', $text);
            } elseif (str_contains($icon, 'fa-envelope-o')) {
                $response['email'] = trim(str_replace('Email:', '', $text));
            } elseif (str_contains($icon, 'fa-calendar')) {
                $response['issue_date'] = trim(str_replace('Ngày cấp:', '', $node->filter('a')->count() > 0 ? $node->filter('a')->text() : $text));
            } elseif (str_contains($icon, 'fa-anchor')) {
                $industryText = $node->filter('a')->count() > 0 ? $node->filter('a')->text() : '';
                $industryCode = preg_match('/industry-(\d+)/', $node->filter('a')->attr('href'), $matches) ? $matches[1] : '';
                $response['main_industry_text'] = trim(str_replace('Ngành nghề chính:', '', $industryText));
                $response['main_industry_code'] = $industryCode;
            } elseif (str_contains($icon, 'fa-info')) {
                $response['status'] = trim(str_replace('Trạng thái:', '', $text));
            } elseif (str_contains($icon, 'fa-clock-o')) {
                $lastUpdated = $node->filter('i')->count() > 1 ? trim($node->filter('i')->last()->text()) : '';
                $response['last_updated'] = trim(str_replace('Cập nhật lần cuối vào', '', $lastUpdated));
            }elseif (str_contains($icon, 'fa-question-circle')) {
                $response['notes'] = $text;
            }
        });

        // Lấy danh sách ngành nghề kinh doanh
        $crawler->filter('ul.hsdn li div div')->each(function (Crawler $node) use (&$response) {
            $code = $node->filter('span')->count() > 0 ? trim($node->filter('span')->text()) : '';
            $text = $node->filter('a')->count() > 0 ? trim($node->filter('a')->text()) : '';
            if ($code && $text) {
                $response['company_industries'][] = [
                    'code' => $code,
                    'text' => $text,
                ];
            }
        });

        return $response;
    }

    public static function insertCompany($dataCrawl, $url){
        // Kiểm tra tax_code đã tồn tại trong bảng company_info hay chưa
        $taxCode = $dataCrawl['tax_code'] ?? null;
        if (empty($taxCode)) {
            throw new \Exception('Dữ liệu crawl thiếu tax_code');
        }

        $existingCompany = Company::where('tax_code', $taxCode)->first();
        if ($existingCompany) {
            Log::info('Tax_code đã tồn tại, bỏ qua xử lý', [
                'tax_code' => $taxCode,
                'company_id' => $existingCompany->id,
                'url' => $url,
            ]);
            return; // Thoát hàm, không tiếp tục xử lý
        }

        $idParent = 83715;
        $title = $dataCrawl['name'] ?? null;
        $taxCode = $dataCrawl['tax_code'] ?? null;
        $taxAddress = $dataCrawl['tax_address'] ?? null;

        if (empty($title) || empty($taxCode)) {
            throw new \Exception('Dữ liệu crawl thiếu title hoặc tax_code');
        }

        $seoTitle = $taxCode . ' - ' . $title;
        $seoDescription = "$title - Mã số thuế $taxCode - Địa chỉ $taxAddress. Thông tin chi tiết về $title, bao gồm mã số thuế, ngày cấp, ngày đóng MST, tên chính thức, tên quốc tế, tên giao dịch, tên viết tắt, trạng thái hoạt động, ngày thành lập, vốn điều lệ, số điện thoại, email, fax, website. Tìm hiểu loại hình doanh nghiệp, nơi đăng ký quản lý, địa chỉ trụ sở, nơi nộp thuế, địa chỉ nhận thông báo thuế, số quyết định thành lập, ngày cấp quyết định, cơ quan cấp phép, giấy phép kinh doanh, ngày nhận tờ khai, thời điểm bắt đầu/kết thúc năm tài chính, mã số hiện thời, ngày hoạt động, hình thức thanh toán, phương pháp tính thuế. Thông tin người đại diện pháp luật (họ tên, năm sinh, giấy tờ tùy thân, địa chỉ), giám đốc, kế toán trưởng, và ngành nghề kinh doanh chính của $title. Dữ liệu chuẩn quốc gia, cập nhật nhanh tại Hợp Tác Kinh Doanh!";
        $slug = Charactor::convertStrToUrl($title).'-'.Charactor::randomString(10);

        // Insert vào bảng seo
        $idSeo = Seo::insertQuick([
            'title' => $title,
            'description' => $seoDescription,
            'level' => 2,
            'parent' => $idParent,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'slug' => $slug,
            'slug_full' => 'danh-ba-doanh-nghiep/'.$slug,
            'type' => 'company_info',
            'rating_author_name' => 1,
            'rating_author_star' => 5,
            'rating_aggregate_count' => rand(10, 2200),
            'rating_aggregate_star' => '4.' . rand(4, 8),
            'language' => 'vi',
        ]);

        if (empty($idSeo)) {
            throw new \Exception('Không thể insert vào bảng seo cho công ty: ' . $title);
        }

        // Chuẩn bị dữ liệu cho bảng company_info
        $insertCompany = ['seo_id' => $idSeo];
        foreach ($dataCrawl as $key => $rowData) {
            if ($key != 'company_industries') {
                if ($key == 'issue_date') {
                    // Convert ngày tháng từ DD/MM/YYYY sang YYYY-MM-DD
                    $insertCompany[$key] = !empty($rowData) ? Carbon::createFromFormat('d/m/Y', $rowData)->format('Y-m-d') : null;
                } else {
                    $insertCompany[$key] = $rowData;
                }
            }
        }

        // thêm phần province_code và province_text
        $addData    = CompanyController::determineProvince($insertCompany['tax_address']);
        $insertCompany = array_merge($insertCompany, $addData);
        
        // Insert vào bảng company_info
        $idCompany = Company::insertItem($insertCompany);

        if (empty($idCompany)) {
            throw new \Exception('Không thể insert vào bảng company_info cho công ty: ' . $title);
        }

        // Insert ngành nghề phụ (nếu có)
        if (!empty($dataCrawl['company_industries']) && is_array($dataCrawl['company_industries'])) {
            foreach ($dataCrawl['company_industries'] as $industry) {
                RelationCompanyInfoIndustryInfo::insertItem([
                    'company_info_id'   => $idCompany,
                    'industry_code'     => $industry['code'],
                ]);
            }
        }

        // Insert vào bảng quan hệ seo_company_info
        RelationSeoCompanyInfo::insertItem([
            'seo_id' => $idSeo,
            'company_info_id' => $idCompany,
        ]);

        return $idCompany;
    }
}
