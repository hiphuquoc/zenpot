<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use App\Models\Blog;

class BlogController extends Controller{

    public static function getBlogFeatured($language) {
        // $cacheKey = 'blog_featured_' . $language;
        // $cacheSeconds = config('app.cache_redis_time', 86400);
        // $useCache = env('APP_CACHE_HTML', true); // Kiểm tra xem có sử dụng cache hay không
    
        // // Nếu sử dụng cache
        // if ($useCache) {
        //     return Cache::remember($cacheKey, now()->addSeconds($cacheSeconds), function () use ($language) {
        //         return self::queryBlogFeatured($language);
        //     });
        // }
    
        // Nếu không sử dụng cache, truy vấn trực tiếp
        return self::queryBlogFeatured($language);
    }
    
    /**
     * Hàm thực hiện truy vấn blog nổi bật.
     *
     * @param string $language
     * @return \Illuminate\Support\Collection
     */
    protected static function queryBlogFeatured($language) {
        return Blog::select('blog_info.*')
            ->join('seo', 'seo.id', '=', 'blog_info.seo_id')
            ->whereHas('seos.infoSeo', function ($query) use ($language) {
                $query->where('language', $language);
            })
            ->where('outstanding', 1)
            ->where('status', 1)
            ->with([
                'seos.infoSeo' => function ($query) use ($language) {
                    $query->where('language', $language);
                },
                'seo',
                'seos'
            ])
            ->orderBy('seo.ordering', 'DESC')
            ->orderBy('id', 'DESC')
            ->skip(1)
            ->take(7)
            ->get();
    }

    public static function getBlogRelatedByBlogId($idBlog, $arrayIdCategoryBlog, $language) {
        // // Tạo khóa cache dựa trên các tham số đầu vào
        // $cacheKey = 'wallpapers_related:' . $idBlog
        //             . ':' . $language
        //             . ':' . md5(json_encode($arrayIdCategoryBlog))
        //             . ':' . $params['loaded']
        //             . ':' . $params['request_load'];
        // $cacheTime = config('app.cache_redis_time', 86400);
        // $useCache = env('APP_CACHE_HTML', true); // Kiểm tra xem có sử dụng cache hay không
    
        // // Nếu sử dụng cache
        // if ($useCache) {
        //     return Cache::remember($cacheKey, now()->addSeconds($cacheTime), function () use ($idBlog, $arrayIdCategoryBlog, $language, $params) {
        //         return self::queryExchangesByProductRelated($idBlog, $arrayIdCategoryBlog, $language, $params);
        //     });
        // }
    
        // Nếu không sử dụng cache, truy vấn trực tiếp
        return self::queryGetBlogRelatedByBlogId($idBlog, $arrayIdCategoryBlog, $language);
    }
    
    /**
     * Hàm thực hiện truy vấn tin đăng liên quan (sắp xếp theo thứ tự có nhiều category_blog_id chung nhất)
     *
     * @param int $idBlog
     * @param array $arrayIdCategoryBlog
     * @param string $language
     * @param array $params
     * @return array
     */
    private static function queryGetBlogRelatedByBlogId($idBlog, $arrayIdCategoryBlog, $language) {
        $response = Blog::select('blog_info.*')
            ->whereHas('seos.infoSeo', function ($query) use ($language) {
                $query->where('language', $language);
            })
            ->join('relation_category_blog_blog_info as rt', 'blog_info.id', '=', 'rt.blog_info_id')
            ->whereIn('rt.category_blog_id', $arrayIdCategoryBlog)
            ->where('blog_info.id', '!=', $idBlog)
            ->selectRaw('COUNT(rt.category_blog_id) as common_categories_count')
            ->groupBy(
                'blog_info.id',
                'blog_info.seo_id',
                'blog_info.outstanding',
                'blog_info.status',
                'blog_info.viewed',
                'blog_info.shared',
                'blog_info.notes',
                'blog_info.created_at',
                'blog_info.updated_at',
            )
            ->orderByDesc('common_categories_count')
            ->with('seo', 'seos')
            ->skip(0)
            ->take(6)
            ->get();
    
        return $response;
    }
    
}
