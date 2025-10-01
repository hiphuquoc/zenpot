<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_info', function (Blueprint $table) {
            $table->id();
            $table->integer('seo_id');
            $table->string('name'); // Tên đầy đủ công ty
            $table->string('international_name')->nullable(); // Tên quốc tế
            $table->string('short_name')->nullable(); // Tên viết tắt
            $table->string('tax_code'); // Mã số thuế
            $table->string('tax_address'); // Địa chỉ thuế
            $table->string('province_code')->nullable(); // Mã tỉnh/thành phố
            $table->string('province_name')->nullable(); // Tên tỉnh/thành phố
            $table->string('legal_representative'); // Người đại diện pháp luật
            $table->string('phone')->nullable(); // Điện thoại
            $table->string('email')->nullable(); // Email
            $table->string('website')->nullable(); // Website
            $table->date('issue_date')->nullable(); // Ngày cấp
            $table->string('main_industry_code', 5)->nullable(); // Mã ngành nghề chính
            $table->string('main_industry_text')->nullable(); // Tên ngành nghề chính
            $table->string('status')->nullable(); // Trạng thái
            $table->string('last_updated')->nullable(); // Ngày cập nhật cuối - dùng text để tránh lỗi
            $table->integer('type_vip')->default(0);
            $table->text('subtitle_vip')->nullable();
            $table->text('file_cloud_logo')->nullable();
            $table->string('url_crawl');
            $table->text('notes')->nullable();
            $table->timestamps();

            // index kết hợp
            $table->index(['province_code', 'issue_date'], 'from_province_code_to_issue_date');
            $table->index(['issue_date', 'province_code'], 'from_issue_date_to_province_code');
        });

        // Thêm index riêng biệt sau khi tạo bảng
        Schema::table('company_info', function (Blueprint $table) {
            $table->unique('seo_id', 'company_info_seo_id_unique');
        });

        // Thêm index độ dài giới hạn bằng raw SQL
        DB::statement('CREATE INDEX company_info_name_index ON company_info (name(191))');
        DB::statement('CREATE INDEX company_info_phone_index ON company_info (phone(191))');
        DB::statement('CREATE UNIQUE INDEX company_info_tax_code_index ON company_info (tax_code(191))');

        // index fulltext cho search
        DB::statement('ALTER TABLE company_info ADD FULLTEXT fulltext_search (tax_code, name, phone)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('company_info');
    }
};
