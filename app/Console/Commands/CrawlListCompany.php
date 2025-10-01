<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\HsctvnComController;

class CrawlListCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl_list:company {pageStart} {pageEnd?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl dữ liệu doanh nghiệp theo trang từ hsctvn.com bắt đầu và trang kết thúc';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $pageStart = $this->argument('pageStart');
        $pageEnd = $this->argument('pageEnd') ?? $pageStart; // nếu không có pageEnd thì dùng pageStart

        // Gọi hàm static trong controller
        HsctvnComController::handleCrawlList($pageStart, $pageEnd);

        $this->info('Crawl hoàn tất từ trang ' . $pageStart . ' đến trang ' . $pageEnd);
    }
}