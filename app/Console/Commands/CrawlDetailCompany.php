<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\MuaBanNetController;

use App\Helpers\CrawlHelper;
use App\Http\Controllers\Admin\HsctvnComController;

class CrawlDetailCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl_detail:company {urlPage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl dữ liệu từ hsctvn.com bằng url';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $urlPage = $this->argument('urlPage');

        $dataCrawls = CrawlHelper::getMultiHtmlByNodeJS([$urlPage]);

        if (empty($dataCrawls)) {
            throw new \Exception('Không thể crawl dữ liệu từ danh sách URLs');
        }

        foreach($dataCrawls as $url => $dataHtml){
            $dataCrawl  = HsctvnComController::getDetail($url, $dataHtml);

            $flag       = HsctvnComController::insertCompany($dataCrawl, $url);

            if(!$flag){
                echo 'Đả tải thành công doanh nghiệp từ trang: '.$urlPage;
            }else {
                echo 'Thất bại, có lỗi xảy ra!';
            }
        }
    }
}