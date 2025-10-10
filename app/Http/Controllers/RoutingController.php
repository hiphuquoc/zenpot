<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Url;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\BlogController;
use App\Models\Industry;
use App\Models\Category;
use App\Models\Page;
use App\Models\CategoryBlog;
use App\Models\CompanyCount;
use App\Models\CompanyIndustry;
use App\Models\CompanyProvince;
use App\Models\Exchange;
use App\Models\ExchangeTag;
use Illuminate\Support\Facades\Auth;

use App\Services\HtmlCacheService;
use App\Services\HeaderMainService;


class RoutingController extends Controller{

    public function routing(Request $request, HtmlCacheService $htmlCacheService)
    {
        // 1. Xử lý đường dẫn và giải mã URL
        $slug = $request->path();
        $decodedSlug = urldecode($slug);
        $tmpSlug = explode('/', $decodedSlug);

        // Loại bỏ phần tử rỗng và các phần không cần thiết (ví dụ: 'public')
        $arraySlug = array_filter($tmpSlug, function ($part) {
            return !empty($part) && $part !== 'public';
        });

        // Loại bỏ hashtag và query string từ phần cuối cùng của đường dẫn
        $lastKey = count($arraySlug) - 1;
        $arraySlug[$lastKey] = preg_replace('#([\?|\#]+).*$#imsU', '', end($arraySlug));
        $urlRequest = implode('/', $arraySlug);

        // 2. Kiểm tra xem URL có tồn tại trong cơ sở dữ liệu không
        $itemSeo = Url::checkUrlExists($urlRequest);

        // // Nếu URL không khớp, redirect về URL chính xác
        // if (!empty($itemSeo->slug_full) && $itemSeo->slug_full !== $urlRequest) {
        //     return Redirect::to($itemSeo->slug_full, 301);
        // }

        // 3. Nếu URL hợp lệ, xử lý tiếp
        if (!empty($itemSeo->type)) {
            // Thiết lập ngôn ngữ và cấu hình theo IP
            $language = $itemSeo->language;
            SettingController::settingLanguage($language);

            // Tạo key cache
            $paramsSlug = request()->only('search', 'page');
            $cacheKey = self::buildNameCache($itemSeo->slug_full, $paramsSlug);

            // Dùng HtmlCacheService để lấy hoặc render
            $htmlContent = $htmlCacheService->getOrRender($cacheKey, function () use ($itemSeo, $language) {
                return $this->fetchDataForRouting($itemSeo, $language);
            });

            echo $htmlContent;
        } else {
            return \App\Http\Controllers\ErrorController::error404();
        }
    }

    /**
     * Hàm hỗ trợ để lấy dữ liệu cho routing và render HTML
     *
     * @param $itemSeo
     * @param $language
     * @param string $menuHtml - HTML của menu được truyền từ bên ngoài
     * @return string|null
     */
    private function fetchDataForRouting($itemSeo, $language, string $menuHtml = ''): ?string
    {
        // Breadcrumb
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        // Thông tin cơ bản
        $modelName = config('tablemysql.' . $itemSeo->type . '.model_name');
        if (!$modelName) return null;

        $modelInstance = resolve("\App\Models\\$modelName");
        $idSeo = $itemSeo->id;

        // lấy html header main menu
        $menuHtml = app(HeaderMainService::class)->getMenuHtml($language);

        // Lấy dữ liệu chính
        $item = $modelInstance::select('*')
            ->whereHas('seos', function ($query) use ($idSeo) {
                $query->where('seo_id', $idSeo);
            })
            ->with('seo', 'seos')
            ->first();

        if (!$item) return null;

        // Thêm menuHtml và breadcrumb vào dữ liệu chung
        $sharedData = compact('menuHtml', 'breadcrumb');

        // dd($itemSeo->type);

        // Xử lý theo từng loại type
        switch ($itemSeo->type) {
            case 'tag_info':
            case 'page_info':
                return $this->handlePageInfo($item, $itemSeo, $language, $sharedData);

            case 'product_info':
                return $this->handleProductInfo($item, $itemSeo, $language, $sharedData);

            case 'category_blog':
                return $this->handleCategoryBlog($item, $itemSeo, $language, $sharedData);

            case 'blog_info':
                return $this->handleBlogInfo($item, $itemSeo, $language, $sharedData);

            default:
                foreach (config("main_" . env('APP_NAME') . ".category_type") as $typeConfig) {
                    if ($itemSeo->type === $typeConfig['key']) {
                        return $this->handleCategoryType($item, $itemSeo, $language, $sharedData);
                    }
                }
        }

        return null; // Trường hợp không khớp type nào
    }

    private function handleProductInfo($item, $itemSeo, $language, array $sharedData) {
        $arrayIdCategory = $item->categories->pluck('category_info_id')->toArray();
        $total = CategoryMoneyController::getWallpapersByProductRelated($item->id, $arrayIdCategory, $language, [
            'loaded' => 0,
            'request_load' => 0,
        ])['total'];

        $dataContent = CategoryMoneyController::buildTocContentMain($itemSeo->contents, $language);

        return view('main.product.index', array_merge([
            'item' => $item,
            'itemSeo' => $itemSeo,
            'language' => $language,
            'total'     => $total,
            'dataContent' => $dataContent,
            'arrayIdCategory' => $arrayIdCategory,
        ], $sharedData))->render();
    }

    private function handlePageInfo($item, $itemSeo, $language, array $sharedData)
    {
        // Trường hợp đặc biệt: Về chúng tôi
        if (!empty($item->type->code) && $item->type->code === 'about_us') {
            $dataContent = CategoryMoneyController::buildTocContentMain($itemSeo->contents, $language);

            return view('main.page.about-us', array_merge([
                'item' => $item,
                'itemSeo' => $itemSeo,
                'language' => $language,
                'dataContent' => $dataContent,
            ], $sharedData))->render();
        }

        // Trường hợp đặc biệt: Liên hệ
        if (!empty($item->type->code) && $item->type->code === 'contact') {
            return view('main.contact.index', array_merge([
                'item' => $item,
                'itemSeo' => $itemSeo,
                'language' => $language,
            ], $sharedData))->render();
        }

        // Mặc định: trang thông thường
        $addTocContent = [];
        if (!empty($itemSeo->faqs) && $itemSeo->faqs->isNotEmpty()) {
            $addTocContent[] = [
                'id' => 'cau-hoi-thuong-gap',
                'name' => 'h2',
                'title' => config("data_language_1.{$language}.question_and_answer"),
            ];
        }

        $dataContent = CategoryMoneyController::buildTocContentMain($itemSeo->contents, $language, $addTocContent);
        $services = Category::select('*')->with('seo', 'seos', 'tags')->get();

        return view('main.page.index', array_merge([
            'item' => $item,
            'itemSeo' => $itemSeo,
            'dataContent' => $dataContent,
            'services' => $services,
            'language' => $language,
        ], $sharedData))->render();
    }

    private function handleCategoryBlog($item, $itemSeo, $language, array $sharedData) 
    {
        $params = [
            'sort_by' => Cookie::get('sort_by') ?? null,
            'array_category_blog_id' => CategoryBlog::getTreeCategoryByInfoCategory($item, [])->pluck('id')->prepend($item->id)->toArray(),
        ];
    
        $blogs = \App\Http\Controllers\CategoryBlogController::getBlogs($params, $language)['blogs'];
        $blogFeatured = BlogController::getBlogFeatured($language);

        return view('main.categoryBlog.index', array_merge([
            'item' => $item,
            'itemSeo' => $itemSeo,
            'language' => $language,
            'blogs' => $blogs, 
            'blogFeatured' => $blogFeatured, 
        ], $sharedData))->render();
    }

    private function handleBlogInfo($item, $itemSeo, $language, array $sharedData) 
    {
        // lấy danh sách post liên quan
        $arrayIdCategoryBlog    = $item->categories->pluck('category_blog_id')->toArray();
        $blogRelated            = BlogController::getBlogRelatedByBlogId($item->id, $arrayIdCategoryBlog, $language);
        // lấy danh sách bài viết nổi bật
        $blogFeatured           = BlogController::getBlogFeatured($language);
        // xây dựng toccontent 
        $dataContent            = CategoryMoneyController::buildTocContentMain($itemSeo->contents, $language);
        $htmlContent            = str_replace('<div id="tocContentMain"></div>', '<div id="tocContentMain">' . $dataContent['toc_content'] . '</div>', $dataContent['content']);

        return view('main.blog.index', array_merge([
            'item' => $item, 
            'itemSeo' => $itemSeo, 
            'blogFeatured' => $blogFeatured, 
            'blogRelated' => $blogRelated, 
            'language' => $language, 
            'htmlContent' => $htmlContent,
        ], $sharedData))->render();
    }

    private function handleCategoryType($item, $itemSeo, $language, array $sharedData) {
        return $this->handlePaidCategory($item, $itemSeo, $language, $sharedData);
    }
    
    private function handlePaidCategory($item, $itemSeo, $language, $sharedData) {
        // Khởi tạo các tham số tìm kiếm
        $arrayIdCategory    = Category::getArrayIdCategoryRelatedByIdCategory($item, [$item->id]);
        // $viewBy             = request()->cookie('view_by') ?? 'each_set';
        $search             = request('search') ?? null;
        $params = [
            'array_category_info_id' => $arrayIdCategory,
            'filters' => request()->get('filters') ?? [],
            'loaded' => 0,
            'request_load' => 1,
            'sort_by' => Cookie::get('sort_by') ?? null,
            'search' => $search,
        ];
    
        // Lấy wallpapers từ controller
        $response = CategoryMoneyController::getWallpapers($params, $language);
    
        // Đảm bảo biến wallpapers luôn tồn tại
        $wallpapers = $response['wallpapers'] ?? [];
        $total = $response['total'] ?? 0;
        $loaded = 0; // lần đầu in ra 0 phần tử
    
        // Xây dựng toc_content
        $dataContent = CategoryMoneyController::buildTocContentMain($itemSeo->contents, $language);
    
        // Render view
        return view('main.category.index', array_merge([
            'item' => $item, 
            'itemSeo' => $itemSeo, 
            'wallpapers' => $wallpapers, 
            'arrayIdCategory' => $arrayIdCategory, 
            'total' => $total, 
            'loaded' => $loaded, 
            'language' => $language, 
            'search' => $search, 
            'dataContent' => $dataContent,
        ], $sharedData))->render();
    }
    
    public static function buildNameCache($slugFull, $params = []){
        $response     = '';
        if(!empty($slugFull)){
             /* xây dựng  slug */
             $tmp    = explode('/', $slugFull);
             $result = [];
             foreach($tmp as $t) if(!empty($t)) $result[] = $t;
             $response = implode('-', $result);
            /* duyệt params để lấy prefix hay # */
            if(!empty($params)){
                $part   = '';
                foreach($params as $key => $param) $part .= $key.'-'.$param;
                if(!empty($part)) $response = $response.'-'.$part;
            }
        }
        return $response;
    }
    
}
