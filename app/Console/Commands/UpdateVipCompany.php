<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\CompanyProvince;

class UpdateVipCompany extends Command {
    
    protected $signature = 'update:vip_company';

    protected $description = 'Chạy job cập nhật lại doanh nghiệp VIP mồi thiếu thông qua command';

    public function handle()
    {
        // Bước 1: Xóa tất cả doanh nghiệp VIP mồi cũ (type_vip = 2)
        Company::where('type_vip', 2)->update(['type_vip' => 0]);
        $this->info("🧹 Đã xóa toàn bộ doanh nghiệp VIP mồi cũ.");

        // Bước 2: Lặp qua từng tỉnh để cập nhật lại doanh nghiệp VIP mồi
        $companyProvinces = CompanyProvince::all();

        foreach ($companyProvinces as $province) {
            $provinceId = $province->province_info_id;

            // Lấy danh sách doanh nghiệp trong tỉnh này, thoả 2 điều kiện:
            // - email rỗng
            // - tên dài hơn 30 ký tự
            $eligibleCompanies = Company::where('province_code', $provinceId)
                ->where(function ($query) {
                    $query->whereNull('email')->orWhere('email', '');
                })
                ->whereRaw('CHAR_LENGTH(name) > 30')
                ->get();

            $total = $eligibleCompanies->count();

            if ($total === 0) {
                $this->warn("⚠️ Không có doanh nghiệp đủ điều kiện tại tỉnh ID: $provinceId");
                continue;
            }

            // Tính số lượng cần chọn ngẫu nhiên (10-15%)
            $percent = rand(10, 15);
            $limit = max(1, round($total * $percent / 100));

            // Chọn ngẫu nhiên
            $selected = $eligibleCompanies->random($limit);

            // Cập nhật type_vip = 2 cho các doanh nghiệp được chọn
            foreach ($selected as $company) {
                $company->update(['type_vip' => 2]);
            }

            $provinceName = $province->infoProvince->name ?? 'Không rõ';
            $this->warn("✅ Tỉnh $provinceName (ID: $provinceId) – đã cập nhật VIP cho $limit / $total doanh nghiệp (không có mail).");
        }

        $this->info("🎉 Hoàn tất cập nhật doanh nghiệp VIP mồi theo tỉnh.");
    }
}
