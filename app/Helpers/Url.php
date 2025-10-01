<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SettingController;
use App\Models\Seo;
use App\Models\EnSeo;

class Url {

    public static function checkUrlExists($slug){
        $infoPage           = new \Illuminate\Database\Eloquent\Collection;
        /* check ngôn ngữ */
        $infoPage           = Seo::select('*')
                                ->where('slug_full', $slug)
                                ->first();
        return $infoPage;
    }

    public static function buildBreadcrumb($slugFull){
        $tmp            = explode('/', $slugFull);
        $result         = new \Illuminate\Database\Eloquent\Collection;
        foreach($tmp as $item){
            $infoItem   = Seo::select('*')
                                ->where('slug', $item)
                                ->first();
            if(empty($infoItem)) return null;
            $result[]   = $infoItem;
        }
        return $result;
    }

    public static function removeUrlParamsAndHash($url) {
        // Phân tích URL thành các thành phần
        $parsedUrl  = parse_url($url);

        // Lấy các phần cần thiết: scheme, host, path
        $scheme     = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host       = $parsedUrl['host'] ?? '';
        $path       = $parsedUrl['path'] ?? '';

        // Ghép lại thành URL sạch (không query và fragment)
        return $scheme . $host . $path;
    }
}