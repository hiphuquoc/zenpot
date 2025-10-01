<?php

namespace App\Http\Controllers;

use App\Helpers\Charactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use App\Models\Page;
use App\Models\Category;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Admin\HelperController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatGptController;
use App\Http\Controllers\Admin\HsctvnComController;
use App\Models\Province;
use App\Models\Tag;
use App\Models\Seo;
use App\Models\SeoContent;
use App\Models\Exchange;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Session;
use App\Models\RelationSeoProductInfo;
use App\Models\RelationSeoCategoryInfo;
use App\Models\RelationSeoTagInfo;
use App\Models\RelationSeoPageInfo;
use App\Models\Timezone;
use App\Jobs\Tmp;
use App\Jobs\AutoTranslateContent;
use App\Jobs\AutoImproveContent;
use App\Jobs\TranslateConfigLanguage;
use App\Jobs\CopyBoxContentToAllTagAndCategory;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendProductMail;
use App\Models\Blog;
use App\Models\Commune;
use App\Models\CompanyProvince;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use App\Services\HeaderMainService;
use App\Services\HtmlCacheService;
use GuzzleHttp\Client;

class HomeController extends Controller {

    public static function home(HeaderMainService $headerMainService, HtmlCacheService $htmlCacheService, Request $request, $language = 'vi')
    {
        SettingController::settingLanguage($language);

        $appName = env('APP_NAME');
        $cacheKey = RoutingController::buildNameCache("{$language}home");

        $htmlContent = $htmlCacheService->getOrRender($cacheKey, function () use ($language, $headerMainService, $appName) {

            // lấy thông tin trang chủ
            $item = Page::select('*')
                        ->whereHas('seos.infoSeo', function ($query) use ($language) {
                            $query->where('slug', $language);
                        })
                        ->with('seo', 'seos.infoSeo', 'type')
                        ->first();

            // lấy html header main menu
            $menuHtml = $headerMainService->getMenuHtml($language);

            // lấy thông tin trang seo
            $itemSeo = self::extractSeoForLanguage($item, $language);
            
            // lấy bài viết mới nhất
            $blogs = Blog::where('status', 1)
                ->with('seo', 'seos')
                ->orderByDesc('id')
                ->skip(0)
                ->take(13)
                ->get();

            // render giao diện
            return View::make('main.home.index', compact(
                'item',
                'itemSeo',
                'menuHtml',
                'blogs',
                'language'
            ))->render();
        });

        echo $htmlContent;
    }

    /**
        * Trích xuất infoSeo đúng ngôn ngữ
    */
    public static function extractSeoForLanguage($item, $language) {
        if (empty($item->seos)) {
            return [];
        }

        foreach ($item->seos as $seo) {
            if (!empty($seo->infoSeo->language) && $seo->infoSeo->language === $language) {
                return $seo->infoSeo;
            }
        }

        return [];
    }

    public function test()
    {
        // $services = [
        //     [
        //         'title' => 'Tour Du Lịch Trong Nước',
        //         'description' => 'Khám phá vẻ đẹp Việt Nam từ núi rừng Tây Bắc đến biển đảo Nam Trung Bộ với các tour trọn gói chất lượng, khởi hành liên tục, giá ưu đãi.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/tour-du-lich-phu-quoc-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ],
        //     [
        //         'title' => 'Tour Du Lịch Quốc Tế',
        //         'description' => 'Trải nghiệm hành trình khám phá thế giới với các tour nước ngoài chọn lọc, hướng dẫn viên chuyên nghiệp, lịch trình hấp dẫn, dịch vụ chu đáo.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/tour-du-lich-anh-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ],
        //     [
        //         'title' => 'Vé Vui Chơi – Giải Trí',
        //         'description' => 'Đặt vé các khu vui chơi, công viên, show diễn hot một cách nhanh chóng – không xếp hàng – nhiều ưu đãi, phù hợp cho cả gia đình và nhóm bạn.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/ve-safari-phu-quoc-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ],
        //     [
        //         'title' => 'Vé Tàu Cao Tốc',
        //         'description' => 'Di chuyển thuận tiện đến Phú Quốc, Côn Đảo, Lý Sơn... với vé tàu cao tốc chính hãng, giờ giấc linh hoạt, hỗ trợ đặt chỗ nhanh chóng.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/tau-sa-ky-ly-son-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ],
        //     [
        //         'title' => 'Vé Máy Bay Giá Tốt',
        //         'description' => 'Săn vé máy bay nội địa và quốc tế với mức giá cạnh tranh, thủ tục đơn giản, hỗ trợ đặt vé – đổi vé – hoàn vé nhanh chóng, uy tín.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/ve-may-bay-phu-yen-di-ha-noi-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ],
        //     [
        //         'title' => 'Đặt Phòng Khách Sạn',
        //         'description' => 'Liên kết hàng nghìn khách sạn từ bình dân đến cao cấp trong và ngoài nước, đặt phòng dễ dàng, giá minh bạch, nhiều chương trình khuyến mãi.',
        //         'image'     => 'https://hitour.vn/storage/images/upload/thien-thanh-phu-quoc-resort-750.webp',
        //         'url'    => 'https://hitour.vn',
        //     ]
        // ];

        // foreach($services as $service){
        //     \App\Models\CompanyService::insertItem([
        //         'company_info_id'   => 128640,
        //         'title'     => $service['title'],
        //         'description'     => $service['description'],
        //         'image'     => $service['image'],
        //         'url'     => $service['url'],
        //     ]);
        // }

        // Danh sách các đoạn YY bạn muốn tạo (theo từng khoảng từ..đến)
        $ranges = [
            [20, 29],
            [32, 39],
            [40, 49]
        ];

        $result = [];

        foreach ($ranges as $range) {
            [$startYY, $endYY] = $range;

            for ($yy = $startYY; $yy <= $endYY; $yy++) {
                for ($xx = 0; $xx <= 99; $xx++) {
                    $result[] = sprintf('%02d%02d', $xx, $yy);
                }
            }
        }

        // In ra kết quả, ngăn cách bằng dấu phẩy
        echo implode(',', $result);

    }


    private static function normalizeUnicode($string) {
        return \Normalizer::normalize($string, \Normalizer::FORM_C);
    }

    public static function callAPIClaudeAI(Request $request){

        // Cấu hình Guzzle client
        $client = new Client();

        // Lấy API key từ .env
        $apiKey = env('CLAUDE_AI_API_KEY');

        // Dữ liệu bạn muốn gửi đến Claude AI API
        $data = [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => '1 + 1 bằng mấy'], 
            ],
        ];

        // Gửi yêu cầu POST đến Claude AI API
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => $data,
        ]);

        // Trả về kết quả từ API dưới dạng JSON
        $result = response()->json(json_decode($response->getBody()->getContents(), true));

        dd($result);
        
    }

    private static function findUniqueElements($arr1, $arr2) {
        // Lọc các phần tử có trong arr1 nhưng không có trong arr2 và ngược lại
        $uniqueInArr1 = array_diff($arr1, $arr2);
        $uniqueInArr2 = array_diff($arr2, $arr1);
        
        // Kết hợp các phần tử không trùng
        return array_merge($uniqueInArr1, $uniqueInArr2);
    }
}