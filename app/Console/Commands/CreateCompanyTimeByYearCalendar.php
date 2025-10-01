<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Seo;
use App\Models\CompanyTime;
use App\Models\RelationSeoCompanyTime;

class CreateCompanyTimeByYearCalendar extends Command
{
    protected $signature = 'create:company_time {yearRange?}';
    protected $description = 'Tạo các trang company_time cho từng năm, tháng, ngày trong khoảng thời gian cho trước';

    public function handle()
    {
        $yearRange = $this->argument('yearRange') ?? Carbon::now()->year;
        $years = $this->parseYearRange($yearRange);

        $infoParent = Seo::where('slug', 'danh-ba-doanh-nghiep')->first();
        if (!$infoParent) {
            $this->error('❌ Không tìm thấy SEO cha với slug "danh-ba-doanh-nghiep"');
            return;
        }
        $idParent = $infoParent->id;

        foreach ($years as $year) {
            $this->line("🔄 Bắt đầu tạo dữ liệu thời gian cho năm $year...", 'info');

            $data = $this->generateDateStructure($year);
            $count = 0;

            foreach ($data as $item) {
                // Tạo SEO
                $title = $item['name'];
                $slug = 'thanh-lap-' . $item['slug'];
                $description = "Doanh nghiệp thành lập $title, thông tin chính thức từ cổng thông tin quốc gia";

                $seo = [
                    'title' => $title,
                    'description' => $description,
                    'level' => 2,
                    'parent' => $idParent,
                    'seo_title' => 'Danh Bạ Doanh Nghiệp ngành ' . $title . ' | Tra Cứu Mã Số Thuế & Thông Tin',
                    'seo_description' => "Danh bạ doanh nghiệp thành lập mới $title: tra cứu mã số thuế, địa chỉ trụ sở, ngành nghề, pháp lý... cập nhật liên tục trên Hoptackinhdoanh.com.",
                    'slug' => $slug,
                    'slug_full' => 'danh-ba-doanh-nghiep/'.$slug,
                    'type' => 'company_time',
                    'rating_author_name' => 1,
                    'rating_author_star' => 5,
                    'rating_aggregate_count' => rand(10, 2200),
                    'rating_aggregate_star' => '4.' . rand(4, 8),
                    'language' => 'vi',
                ];

                $idSeo = Seo::insertQuick($seo);

                if ($idSeo) {
                    $idCompanyTime = CompanyTime::insertItem([
                        'seo_id' => $idSeo,
                        'date_start' => $item['date_start'],
                        'date_end' => $item['date_end'],
                    ]);

                    RelationSeoCompanyTime::insertItem([
                        'seo_id' => $idSeo,
                        'company_time_id' => $idCompanyTime,
                    ]);

                    $this->warn("✅ Đã tạo: " . $item['name'], 'comment');
                    $count++;
                }
            }

            $this->info("🎯 Hoàn tất năm $year – Tổng mục đã tạo: $count");
        }

        $this->info("✅ Tất cả đã hoàn thành.");
    }

    private function parseYearRange(string $yearRange): array
    {
        // Nếu là 1 năm
        if (is_numeric($yearRange)) {
            return [(int) $yearRange];
        }

        // Nếu là khoảng năm, ví dụ 2010-2025
        if (preg_match('/^(\d{4})-(\d{4})$/', $yearRange, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            return range($start, $end);
        }

        throw new \InvalidArgumentException("⚠️ Tham số năm không hợp lệ. Dùng: 2025 hoặc 2010-2025");
    }

    private function generateDateStructure(int $year): array
    {
        $result = [];

        // Năm
        $result[] = [
            'name' => "năm $year",
            'slug' => "nam-$year",
            'date_start' => "$year-01-01",
            'date_end' => "$year-12-31",
        ];

        // Tháng + Ngày
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $dateStart = Carbon::create($year, $month, 1);
            $dateEnd = $dateStart->copy()->endOfMonth();

            // Tháng
            $result[] = [
                'name' => "tháng $monthStr-$year",
                'slug' => "thang-$monthStr-$year",
                'date_start' => $dateStart->toDateString(),
                'date_end' => $dateEnd->toDateString(),
            ];

            // Ngày
            for ($day = 1; $day <= $dateEnd->day; $day++) {
                $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
                $date = Carbon::create($year, $month, $day);
                $formatted = $date->format('d-m-Y');

                $result[] = [
                    'name' => "ngày $formatted",
                    'slug' => "ngay-" . str_replace('-', '-', $formatted),
                    'date_start' => $date->toDateString(),
                    'date_end' => $date->toDateString(),
                ];
            }
        }

        return $result;
    }
}
