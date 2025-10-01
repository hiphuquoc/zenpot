<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\HsctvnComController;
use App\Http\Controllers\Admin\CompanyController;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Seo;
use App\Models\Province;
use App\Models\CompanyIndustry;
use App\Helpers\Charactor;
use App\Helpers\CrawlHelper;
use App\Models\CompanyProvince;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Jobs\CrawlDetailCompanyMulti;
use App\Models\Company;
use App\Models\RelationSeoCompanyProvince;

class RunTmpJob extends Command {
    
    protected $signature = 'run:tmp';

    protected $description = 'Chạy job Tmp thông qua command';

    // public function handle()
    // {
    //     $this->info("🔎 Đang xử lý 1000 bản ghi...");

    //     $records = Seo::where(function($q) {
    //             $q->where('slug', 'like', 'thong-tin-doanh-nghiep%')
    //             ->orWhere('slug_full', 'like', 'thong-tin-doanh-nghiep%');
    //         })
    //         ->orderBy('id')
    //         ->limit(1000)
    //         ->get();

    //     $count = 0;

    //     foreach ($records as $record) {
    //         $oldSlug     = $record->slug;
    //         $oldSlugFull = $record->slug_full;

    //         $record->slug = str_replace('thong-tin-doanh-nghiep', 'danh-ba-doanh-nghiep', $record->slug);
    //         $record->slug_full = str_replace('thong-tin-doanh-nghiep', 'danh-ba-doanh-nghiep', $record->slug_full);
    //         $record->updateQuietly();

    //         $count++;
    //     }

    //     $this->info("✅ Đã xử lý {$count} bản ghi.");
    // }

    public function handle()
    {
        
    }

}
