<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\MuaBanNetController;

class CrawlDetailMuaBanNet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl_detail:muabannet {urlPage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl dữ liệu từ muaban.net bằng url';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $urlPage = $this->argument('urlPage');

        // Gọi hàm static trong controller
        MuaBanNetController::handleCrawlDetail($urlPage);

        $this->info('Crawl hoàn tất trang ' . $urlPage);
    }
}