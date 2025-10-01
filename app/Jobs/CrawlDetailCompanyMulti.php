<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Controllers\Admin\HsctvnComController;
use App\Helpers\CrawlHelper;
use App\Models\CompanyLog;

class CrawlDetailCompanyMulti implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $urls;
    public  $tries = 2; // Số lần thử lại

    public function __construct($urls){
        $this->urls  = $urls;
    }

    public function handle()
    {
        try {
            $dataCrawls = CrawlHelper::getMultiHtmlByNodeJS($this->urls);

            if (empty($dataCrawls)) {
                foreach ($this->urls as $url) {
                    CompanyLog::insertItem([
                        'url_crawl' => $url,
                        'error' => 'Không thể crawl dữ liệu từ URL',
                        'created_at' => now(),
                    ]);
                }
                throw new \Exception('Không thể crawl dữ liệu từ danh sách URLs');
            }

            foreach ($dataCrawls as $url => $dataHtml) {
                try {
                    $dataCrawl = HsctvnComController::getDetail($url, $dataHtml);
                    $idCompany = HsctvnComController::insertCompany($dataCrawl, $url);

                    if (empty($idCompany)) {
                        CompanyLog::insertItem([
                            'url_crawl' => $url,
                            'error' => 'Không thể insert công ty từ dữ liệu crawl',
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    CompanyLog::insertItem([
                        'url_crawl' => $url,
                        'error' => $e->getMessage(),
                        'created_at' => now(),
                    ]);
                    // Tiếp tục với URL tiếp theo thay vì thất bại toàn bộ job
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Ghi log lỗi tổng quát nếu cần
            \Log::error('Lỗi trong job CrawlDetailCompanyMulti: ' . $e->getMessage());
            throw $e; // Ném lại ngoại lệ để job thất bại và Supervisor xử lý
        }
    }
}
