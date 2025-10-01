<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Helpers\Image;
use App\Http\Controllers\Admin\HelperController;
use App\Models\Seo;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    const MAX_ITEMS = 500;

    public static function main()
    {
        $types = config('tablemysql');
        $now = now()->toIso8601String();

        $sitemaps = array_filter($types, fn($type) => !empty($type['sitemap']));

        $entries = array_map(function ($key) use ($now) {
            $url = self::escapeXml(env('APP_URL') . "/sitemap/{$key}.xml");
            return self::generateSitemapEntry($url, $now);
        }, array_keys($sitemaps));

        return self::sitemapIndexResponse($entries);
    }

    public static function child($type)
    {
        if (empty($type)) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $type = HelperController::determinePageType($type);
        $now = now()->toIso8601String();

        $entries = [];
        foreach (config('language') as $lang) {
            if (!empty($lang['key'])) {
                $url = self::escapeXml(env('APP_URL') . "/sitemap/{$lang['key']}/{$type}.xml");
                $entries[] = self::generateSitemapEntry($url, $now);
            }
        }

        return self::sitemapIndexResponse($entries);
    }

    public static function childForLanguage($language, $typeWithPage)
    {
        if (empty($typeWithPage)) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        if (preg_match('/^(.*)-(\d+)$/', $typeWithPage, $matches)) {
            $type = $matches[1];
            $page = (int) $matches[2];

            if (str_contains($type, 'index')) {
                $realType = str_replace('-index', '', $type);
                return self::childIndexPage($language, $realType, $page);
            }

            return self::childForLanguagePage($language, $type, $page);
        }

        $type = $typeWithPage;
        $modelName = config("tablemysql.{$type}.model_name");
        if (!$modelName) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $model = resolve("\\App\\Models\\{$modelName}");
        $total = $model::count();

        $totalPages = ceil($total / self::MAX_ITEMS);
        $totalIndexPages = ceil($totalPages / self::MAX_ITEMS);
        $now = now()->toIso8601String();

        $entries = [];

        if ($totalIndexPages > 1) {
            for ($i = 1; $i <= $totalIndexPages; $i++) {
                $url = self::escapeXml(env('APP_URL') . "/sitemap/{$language}/{$type}-index-{$i}.xml");
                $entries[] = self::generateSitemapEntry($url, $now);
            }
        } else {
            for ($i = 1; $i <= $totalPages; $i++) {
                $url = self::escapeXml(env('APP_URL') . "/sitemap/{$language}/{$type}-{$i}.xml");
                $entries[] = self::generateSitemapEntry($url, $now);
            }
        }

        return self::sitemapIndexResponse($entries);
    }

    public static function childForLanguagePage($language, $type, $page)
    {
        $page = max(1, (int) $page);
        $modelName = config("tablemysql.{$type}.model_name");

        if (!$modelName) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $model = resolve("\\App\\Models\\{$modelName}");

        // $items = $model::select('*')
        //     ->withDefaultSeoForLanguage($language)
        //     ->orderByDesc('id')
        //     ->offset(($page - 1) * self::MAX_ITEMS)
        //     ->limit(self::MAX_ITEMS)
        //     ->get();

        $items = DB::table('company_info as c')
                    ->join('relation_seo_company_info as r', 'c.id', '=', 'r.company_info_id')
                    ->join('seo as s', 's.id', '=', 'r.seo_id')
                    ->where('s.language', $language)
                    ->orderByDesc('c.id')
                    ->select([
                        'c.id',
                        's.slug_full',
                        's.updated_at',
                        's.seo_title',
                        's.image',
                    ])
                    ->offset(($page - 1) * self::MAX_ITEMS)
                    ->limit(self::MAX_ITEMS)
                    ->get();


        if ($items->isEmpty()) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $entries = [];

        foreach ($items as $item) {
            $seo = optional($item->seos->first())->infoSeo;
            if (!$seo) continue;

            $url = self::escapeXml(env('APP_URL') . '/' . ltrim($seo->slug_full, '/'));
            $lastmod = self::escapeXml(date('c', strtotime($seo->updated_at)));
            $title = self::escapeXml($seo->seo_title);
            $image = config('image.default');

            if (!empty($item->seo->image)) {
                $image = Image::getUrlImageLargeByUrlImage($item->seo->image);
            }

            $image = self::escapeXml($image);

            $entries[] = <<<XML
                            <url>
                                <loc>{$url}</loc>
                                <lastmod>{$lastmod}</lastmod>
                                <changefreq>weekly</changefreq>
                                <priority>1.0</priority>
                                <image:image>
                                    <image:loc>{$image}</image:loc>
                                    <image:title>{$title}</image:title>
                                </image:image>
                            </url>
                            XML;
        }

        return self::urlsetResponse($entries);
    }

    public static function childIndexPage($language, $type, $indexPage)
    {
        $indexPage = max(1, (int) $indexPage);
        $modelName = config("tablemysql.{$type}.model_name");

        if (!$modelName) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $model = resolve("\\App\\Models\\{$modelName}");
        $total = $model::count();
        $totalPages = ceil($total / self::MAX_ITEMS);

        $start = ($indexPage - 1) * self::MAX_ITEMS + 1;
        $end = min($totalPages, $indexPage * self::MAX_ITEMS);

        if ($start > $end || $start < 1) {
            return \App\Http\Controllers\ErrorController::error404();
        }

        $now = now()->toIso8601String();
        $entries = [];

        for ($i = $start; $i <= $end; $i++) {
            $url = self::escapeXml(env('APP_URL') . "/sitemap/{$language}/{$type}-{$i}.xml");
            $entries[] = self::generateSitemapEntry($url, $now);
        }

        return self::sitemapIndexResponse($entries);
    }

    private static function generateSitemapEntry(string $loc, string $lastmod): string
    {
        return <<<XML
            <sitemap>
                <loc>{$loc}</loc>
                <lastmod>{$lastmod}</lastmod>
            </sitemap>
            XML;
    }

    private static function sitemapIndexResponse(array $entries)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= implode("\n", $entries);
        $xml .= "\n</sitemapindex>";

        return response($xml)->header('Content-Type', 'application/xml');
    }

    private static function urlsetResponse(array $entries)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        $xml .= implode("\n", $entries);
        $xml .= "\n</urlset>";

        return response($xml)->header('Content-Type', 'application/xml');
    }

    private static function escapeXml(string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}