<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exchange;
use App\Models\Post;

class PostController extends Controller {

    public static function loadPostForPage(Request $request) {
        $id         = $request->get('id') ?? 0;
        $type       = $request->get('type') ?? '';
        $page       = $request->get('page') ?? 1;
        $language   = $request->get('language') ?? '';
        $search     = $request->get('search') ?? '';

        $content    = '';

        // kiểm tra trước khi thực hiện
        if(empty($id)||empty($type)||empty($language)) {
            return false;
        }
        
        // xử lý cho trang exchange_info
        if($type=='exchange_info'){
            $infoExchange       = Exchange::find($id);
            $arrayIdExchange    = Exchange::getArrayIdExchangeRelatedByIdExchange($infoExchange, [$id]);
            $params = [
                'search' => $search,
                'page' => $page,
                'per_page' => config("main_" . env('APP_NAME') . ".paginate.per_page"),
                'array_exchange_info_id' => $arrayIdExchange,
            ];
            $posts              = ExchangeController::getExchanges($params, $language);
        }
        
        // xử lý cho trang exchange_tag
        if($type=='exchange_tag'){
            $params = [
                'search' => $search,
                'page' => $page,
                'per_page' => config("main_" . env('APP_NAME') . ".paginate.per_page"),
                'array_exchange_tag_id' => [$id],
            ];
            $posts              = ExchangeController::getExchanges($params, $language);
        }

        // xử lý cho tranh post_info (tải post liên quan)
        if($type=='post_info'){
            // $params = [
            //     'page' => 6,
            //     'per_page' => config("main_" . env('APP_NAME') . ".paginate.per_page"),
            //     'array_exchange_tag_id' => [$id],
            // ];
            // $posts              = ExchangeController::getExchanges($params, $language);
            // lấy danh sách post liên quan
            $infoPost           = Post::find($id);
            $arrayIdExchangeTag = $infoPost->exchangeTags->pluck('exchange_tag_id')->toArray();
            $posts              = ExchangeController::getPostRelatedByPostId($infoPost->id, $arrayIdExchangeTag, $language);
        }

        // xử lý cho trang chủ
        if($type=='page_info'){
            $params = [
                'page' => $page,
                'per_page' => 9,
            ];
            $posts              = ExchangeController::getExchanges($params, $language);
        }

        // xử lý cho trang company_info (bài đăng của doanh nghiệp VIP)
        if($type=='company_info'){
            $posts              = Post::select('*')
                                    ->where('company_info_id', $id)
                                    ->get();
        }

        // lấy nội dung xhtml
        foreach($posts as $post){
            $content            .= view('main.exchange.item', [
                                        'post' => $post,
                                        'language'  => $language,
                                    ])->render();
        }

        // lấy paginate xhtml
        $urlSource = $request->headers->get('referer');
        // Loại bỏ tham số page nếu tồn tại
        if (!empty($urlSource)) {
            $urlSource = self::removeQueryParam($urlSource, 'page');
        }
        $paginate   = view('main.snippets.paginate', [
                            'data' => $posts,
                            'urlSource' => $urlSource,
                        ])->render();
        
        return response()->json([
            'content'   => $content,
            'total'     => is_object($posts) && method_exists($posts, 'total') ? $posts->total() : 0,
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
