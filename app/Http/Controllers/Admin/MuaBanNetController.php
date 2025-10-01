<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ChatGptController;
use Intervention\Image\ImageManagerStatic;
use App\Helpers\Charactor;
use App\Helpers\CrawlHelper;
use App\Helpers\Upload;
use App\Helpers\Url;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Seo;
use App\Models\Post;
use App\Models\Exchange;
use App\Models\ExchangeTag;
use App\Models\Crawl;
use App\Models\SystemFile;

use Symfony\Component\Mime\MimeTypes;
use App\Http\Requests\PostRequest;

use Illuminate\Support\Facades\Log;

use App\Jobs\UploadPostCrawl;

class MuaBanNetController extends Controller {

    public static function handleCrawlList($pageStart, $pageEnd = '') {
        if (empty($pageEnd)) {
            $pageEnd = $pageStart;
        }

        // Xác định lại thứ tự để crawl từ trang lớn đến trang nhỏ
        $start = max($pageStart, $pageEnd);
        $end = min($pageStart, $pageEnd);

        for ($i = $start; $i >= $end; --$i) {
            $urlList = "https://muaban.net/doi-tac?page={$i}";

            $dataCrawl = self::crawlList($urlList);

            if (!empty($dataCrawl)) {
                $count = 0;
                foreach ($dataCrawl as $urlCrawl) {
                    self::handleCrawlDetail($urlCrawl);
                    $count++;
                }

                echo "Đã Crawl {$count} bài đăng của trang https://muaban.net/doi-tac?page={$i}\n";
            } else {
                echo "Không tìm thấy bài đăng nào ở trang {$i}\n";
            }
        }
    }

    public static function crawlList($urlList)
    {
        $response   = [];

        if (!$urlList) {
            return $response; // Trả về mảng rỗng nếu URL không hợp lệ
        }

        // hàm lấy html - xử lý nhiều công cụ
        $html       = CrawlHelper::getHtml($urlList, false);

        if (!$html) {
            return "Lỗi: Không thể lấy HTML từ URL.";
        }

        try {
            // Dùng DomCrawler để parse nội dung
            $crawler = new Crawler($html);

            // Lấy danh sách URL từ các phần tử bài đăng
            $crawler->filter('div.sc-q9qagu-2.FOmwc div.ieSUOT')->each(function (Crawler $node) use (&$response) {
                $ahrefNode = $node->filter('a.over')->first();
                if ($ahrefNode->count() > 0) {
                    $href = $ahrefNode->attr('href');
                    // Kiểm tra và xử lý URL
                    if ($href && !in_array($href, $response)) {
                        // Nếu href không bắt đầu bằng 'https://', thêm 'https://muaban.net'
                        $fullUrl = strpos($href, 'http') === 0 ? $href : 'https://muaban.net' . $href;
                        $response[] = $fullUrl;
                    }
                }
            });

            // Loại bỏ trùng lặp (nếu cần)
            $response = array_unique($response);
        } catch (\Exception $e) {
            Log::error("Lỗi khi parse HTML từ URL: {$urlList}. Lỗi: {$e->getMessage()}");
        }

        return $response;
    }

    public static function handleCrawlDetail($urlCrawl) {
        $urlCrawl = Url::removeUrlParamsAndHash($urlCrawl);

        $checkCrawl = Crawl::select('id')
            ->where('url', $urlCrawl)
            ->first();

        if (empty($checkCrawl->id)) {
            // Crawl dữ liệu chi tiết
            $dataCrawl = self::crawlDetail($urlCrawl);

            // Lưu vào database
            $idCrawl = Crawl::insertItem([
                'url'           => $urlCrawl,
                'title'         => $dataCrawl['title'] ?? '',
                'location'      => $dataCrawl['location'] ?? '',
                'contact_name'  => $dataCrawl['contact_name'] ?? '',
                'contact_phone' => $dataCrawl['contact_phone'] ?? '',
                'image_urls'    => json_encode($dataCrawl['image_urls'] ?? []),
                'content'       => $dataCrawl['content'] ?? '',
            ]);

            if ($idCrawl) {
                UploadPostCrawl::dispatch($idCrawl);

                echo "Crawl thành công: {$urlCrawl}\n";
                return true;
            }

            echo "Crawl tin thất bại: {$urlCrawl}\n";
            return false;
        }

        echo "Url này đã được crawl trước đó: {$urlCrawl}\n";
        return false;
    }

    public static function crawlDetail($urlCrawl){
        if(!$urlCrawl) return [];

        // Gọi script Node.js để render JS và trả về HTML
        $escapedUrl = escapeshellarg($urlCrawl);
        $scriptPath = base_path('scripts/muabannet_crawlDetail.cjs'); // .cjs tránh lỗi (đã xử lý)

        // Chạy script Node.js và nhận HTML sau khi JS chạy xong
        $html = shell_exec("node {$scriptPath} {$escapedUrl}");

        if (!$html) {
            return "Lỗi: Không thể lấy HTML từ URL.";
        }

        // Dùng DomCrawler để parse nội dung
        $crawler = new Crawler($html);

        // Lấy tiêu đề h1
        $title = '';
        if ($crawler->filter('h1')->count() > 0) {
            $title = $crawler->filter('h1')->first()->text();
        }

        // Lấy nội dung bài viết (.khOhZD)
        $contentHtml = '';
        if ($crawler->filter('.khOhZD')->count() > 0) {
            $contentHtml = $crawler->filter('.khOhZD')->first()->html();
            $contentHtml = self::replacePhoneNumbers($contentHtml); // Thay số điện thoại
        }

        // Lấy tên người đăng (.jqjOtu)
        $contactName = '';
        if ($crawler->filter('.jqjOtu')->count() > 0) {
            $contactName = $crawler->filter('.jqjOtu')->first()->text();
            $contactName = str_replace('Xem trang cá nhân', '', $contactName); /* xóa text thừa */
            $contactName = str_replace('Khách hàng thân thiết', '', $contactName); /* xóa text thừa */
        }

        // Lấy sdt người đăng (.bsEnxf .main)
        $contactPhone = '';
        if ($crawler->filter('.bsEnxf .main')->count() > 0) {
            $contactPhone = $crawler->filter('.bsEnxf .main')->first()->text();
            $contactPhone = str_replace('Bấm để hiện số', '', $contactPhone); /* xóa text thừa */
        }

        // Lấy tên khu vực
        $location = '';
        if ($crawler->filter('.chPxcj .address')->count() > 0) {
            $location = $crawler->filter('.chPxcj .address')->first()->text();
        }

        // Lấy danh sách url image (.slick-slider .slick-track)
        $imageUrls = [];
        $crawler->filter('.slick-slider .slick-track img')->each(function (Crawler $node) use (&$imageUrls) {
            $src = $node->attr('src');
            if ($src) {
                $imageUrls[] = $src;
            }
        });

        // Kết quả
        $response = [
            'title' => $title,
            'location' => $location,
            'content' => $contentHtml,
            'contact_name' => $contactName,
            'contact_phone' => $contactPhone,
            'image_urls' => $imageUrls,
        ];

        return $response;
    }

    /* helpers */
    private static function replacePhoneNumbers($html){ /* cấu trúc cho riêng trang muaban.net */
        $crawler = new Crawler($html);

        // Lọc các phần tử có class phone-wrapper
        $crawler->filter('.phone-wrapper')->each(function (Crawler $node) use (&$html) {

            // Tìm thẻ con có class phone-hidden chứa data-phone
            $phoneHiddenNode = $node->filter('.phone-hidden')->first();

            if ($phoneHiddenNode->count() > 0) {

                // Lấy số thật từ data-phone
                $realPhone = $phoneHiddenNode->attr('data-phone');

                if ($realPhone) {
                    // Lấy chuỗi hiển thị (ví dụ: 093414****)
                    $displayNode = $phoneHiddenNode->filter('span')->first();

                    if ($displayNode->count() > 0) {
                        $displayPhone = $node->html();

                        // Thay thế chuỗi bị ẩn bằng số thật
                        $html = str_replace($displayPhone, $realPhone, $html);
                    }
                }
            }
        });

        return $html;
    }
    
}
