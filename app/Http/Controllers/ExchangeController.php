<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ExchangeController extends Controller {

    public static function getExchanges($params, $language) {
        // $cacheKey = 'wallpapers:' . md5(json_encode($params) . $language);
        // $cacheTime = config('app.cache_redis_time', 86400);
        // $useCache = env('APP_CACHE_HTML', true);
    
        // // Kiểm tra xem có sử dụng cache hay không
        // if ($useCache) {
        //     return Cache::remember($cacheKey, now()->addSeconds($cacheTime), function () use ($params, $language) {
        //         return self::queryExchanges($params, $language);
        //     });
        // }

        // Nếu không sử dụng cache, truy vấn trực tiếp
        return self::queryExchanges($params, $language);
    }
    
    private static function queryExchanges($params, $language) {
        $keySearch = $params['search'] ?? null;
        $currentPage = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 10;
        $arrayIdExchange = $params['array_exchange_info_id'] ?? [];
        $arrayIdExchangeTag = $params['array_exchange_tag_id'] ?? [];

        // Laravel pagination sẽ tự động dùng page thông qua Resolver
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        // Khởi tạo query
        $query = Post::select('post_info.*')
            ->join('seo', 'seo.id', '=', 'post_info.seo_id')
            ->whereHas('seos.infoSeo', function ($subQuery) use ($language, $keySearch) {
                $subQuery->where('language', $language);
            });
        // === THAY ĐỔI TẠI ĐÂY: DÙNG MEILISEARCH ĐỂ LỌC THEO TITLE ===
        if ($keySearch) {
            $ids = Post::search($keySearch)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            if (empty($ids)) {
                $query->whereRaw('0=1'); // Không có kết quả nào
            } else {
                $query->whereIn('post_info.id', $ids);
            }
        }
        // =========================================================
        $query->when(!empty($arrayIdExchange), function ($subQuery) use ($arrayIdExchange) {
                $subQuery->whereHas('exchanges', function ($subQueryLv2) use ($arrayIdExchange) {
                    $subQueryLv2->whereIn('exchange_info_id', $arrayIdExchange);
                });
            })
            ->when(!empty($arrayIdExchangeTag), function ($subQuery) use ($arrayIdExchangeTag) {
                $subQuery->whereHas('exchangeTags', function ($subQueryLv2) use ($arrayIdExchangeTag) {
                    $subQueryLv2->whereIn('exchange_tag_id', $arrayIdExchangeTag);
                });
            });

        // Phân trang
        $posts = $query
            ->orderBy('post_info.type_vip', 'desc')
            ->orderBy('post_info.outstanding', 'desc')
            ->orderBy('post_info.updated_at', 'desc')
            ->withDefaultSeoForLanguage($language)
            ->with('seo')
            ->paginate($perPage);

        return $posts;
    }

    public static function getPostRelatedByPostId($idPost, $arrayIdExchangeTag, $language) {
        // // Tạo khóa cache dựa trên các tham số đầu vào
        // $cacheKey = 'wallpapers_related:' . $idPost
        //             . ':' . $language
        //             . ':' . md5(json_encode($arrayIdExchangeTag))
        //             . ':' . $params['loaded']
        //             . ':' . $params['request_load'];
        // $cacheTime = config('app.cache_redis_time', 86400);
        // $useCache = env('APP_CACHE_HTML', true); // Kiểm tra xem có sử dụng cache hay không
    
        // // Nếu sử dụng cache
        // if ($useCache) {
        //     return Cache::remember($cacheKey, now()->addSeconds($cacheTime), function () use ($idPost, $arrayIdExchangeTag, $language, $params) {
        //         return self::queryExchangesByProductRelated($idPost, $arrayIdExchangeTag, $language, $params);
        //     });
        // }
    
        // Nếu không sử dụng cache, truy vấn trực tiếp
        return self::queryGetPostRelatedByPostId($idPost, $arrayIdExchangeTag, $language);
    }
    
    /**
     * Hàm thực hiện truy vấn tin đăng liên quan (sắp xếp theo thứ tự có nhiều exchange_tag chung nhất)
     *
     * @param int $idPost
     * @param array $arrayIdExchangeTag
     * @param string $language
     * @param array $params
     * @return array
     */
    private static function queryGetPostRelatedByPostId($idPost, $arrayIdExchangeTag, $language) {
        $response = Post::select('post_info.*')
            ->whereHas('seos.infoSeo', function ($query) use ($language) {
                $query->where('language', $language);
            })
            ->join('relation_exchange_tag_post_info as rt', 'post_info.id', '=', 'rt.post_info_id')
            ->whereIn('rt.exchange_tag_id', $arrayIdExchangeTag)
            ->where('post_info.id', '!=', $idPost)
            ->selectRaw('COUNT(rt.exchange_tag_id) as common_tags_count')
            ->groupBy(
                'post_info.id',
                'post_info.seo_id',
                'post_info.logo',
                'post_info.type_vip',
                'post_info.ribbon',
                'post_info.outstanding',
                'post_info.status',
                'post_info.viewed',
                'post_info.notes',
                'post_info.created_at',
                'post_info.updated_at',
            )
            ->orderByDesc('common_tags_count')
            ->withDefaultSeoForLanguage($language)
            ->with('seo')
            ->skip(0)
            ->take(6)
            ->get();
    
        return $response;
    }
}
