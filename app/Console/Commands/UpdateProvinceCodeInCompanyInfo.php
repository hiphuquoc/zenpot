<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Http\Controllers\Admin\CompanyController;

class UpdateProvinceCodeInCompanyInfo extends Command {
    
    protected $signature = 'update:province_code';

    protected $description = 'Chạy job cập nhật lại province_code và province_text (thông qua tax_address) cho những doanh nghiệp thiếu thông qua command';

    public function handle()
    {
        // Lấy 1000 doanh nghiệp ngẫu nhiên chưa có province_name nhưng có địa chỉ
        $companies  = Company::select('*')
                        ->where('province_name', '')
                        ->where('tax_address', '!=', '')
                        ->inRandomOrder()
                        ->limit(1000)
                        ->get();

        $count      = 0;
        foreach($companies as $company){
            $update = CompanyController::determineProvince($company->tax_address);
            $flag   = Company::updateItem($company->id, $update);

            if(!empty($flag)) ++$count;
        }
        
        $this->info("✅ Đã cập nhật province_code cho {$count} doanh nghiệp.");
    }

}
