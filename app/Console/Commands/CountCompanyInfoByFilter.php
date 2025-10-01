<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\CompanyIndustry;
use App\Models\CompanyProvince;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Company;
use App\Models\CompanyCount;
use App\Models\Industry;

class CountCompanyInfoByFilter extends Command
{
    protected $signature = 'count:company_info';

    protected $description = 'Chạy job để đếm các công ty theo từng loại filter (lưu trước trong CSDL - để lấy ra nhanh hơn) thông qua command';

    public function handle()
    {
        $this->warn("=== Bắt đầu đếm doanh nghiệp theo tỉnh thành ===");
        $provinces  = CompanyProvince::select('*')
            ->withCount('companies')
            ->withCount([
                'companies as vip_companies_of_province' => function ($query) {
                    $query->where('type_vip', '>', 0);
                }
            ])
            ->with('seo')
            ->get();

        foreach ($provinces as $province) {
            $total = $province->companies_count ?? 0;
            $totalVip = $province->vip_companies_of_province ?? 0;
            $referenceId = $province->id;
            $referenceType = $province->seo->type;

            CompanyCount::updateOrInsert(
                [
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType
                ],
                [
                    'total' => $total,
                ]
            );

            CompanyCount::updateOrInsert(
                [
                    'reference_id' => $referenceId,
                    'reference_type' => 'company_gold_of_province'
                ],
                [
                    'total' => $totalVip,
                ]
            );

            $this->info("Đã xử lý tỉnh: {$province->name} | Tổng: {$total} | VIP: {$totalVip}");
        }
        $this->warn("✓ Hoàn tất đếm doanh nghiệp theo tỉnh thành");

        // ----------------------------------------------------------------------

        $this->warn("=== Bắt đầu đếm doanh nghiệp theo ngành nghề ===");
        $industries = CompanyIndustry::select('*')
            ->with('seo', 'infoIndustry')
            ->get();

        foreach ($industries as $industry) {
            $arrayIdIndustry = Industry::getLevelFourChildrenByCode($industry->infoIndustry->code)->toArray();

            $total = Company::whereHas('industries', function ($query) use ($arrayIdIndustry) {
                $query->whereIn('industry_code', $arrayIdIndustry);
            })->count();

            $totalVip = Company::where('type_vip', '>', 0)
                ->whereHas('industries', function ($query) use ($arrayIdIndustry) {
                    $query->whereIn('industry_code', $arrayIdIndustry);
                })->count();

            $referenceId = $industry->id;
            $referenceType = $industry->seo->type;

            CompanyCount::updateOrInsert(
                [
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType
                ],
                [
                    'total' => $total,
                ]
            );

            CompanyCount::updateOrInsert(
                [
                    'reference_id' => $referenceId,
                    'reference_type' => 'company_gold_of_industry'
                ],
                [
                    'total' => $totalVip,
                ]
            );

            $this->info("Đã xử lý ngành: {$industry->infoIndustry->name} | Tổng: {$total} | VIP: {$totalVip}");
        }
        $this->warn("✓ Hoàn tất đếm doanh nghiệp theo ngành nghề");

        // ----------------------------------------------------------------------

        $this->warn("=== Bắt đầu đếm phường xã theo tỉnh ===");
        $provinceSources = Province::select('*')
            ->with('communes')
            ->get();

        foreach ($provinceSources as $province) {
            $total = $province->communes->count() ?? 0;
            $referenceId = $province->id;
            $referenceType = 'province_info';

            CompanyCount::updateOrInsert(
                [
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType
                ],
                [
                    'total' => $total,
                ]
            );

            $this->info("Đã xử lý phường/xã tỉnh: {$province->name} | Tổng số: {$total}");
        }
        $this->warn("✓ Hoàn tất đếm phường xã theo tỉnh");

        // Tổng kết
        $this->line('');
        $this->warn("🎯 Toàn bộ quá trình đếm dữ liệu đã hoàn tất!");
    }
}