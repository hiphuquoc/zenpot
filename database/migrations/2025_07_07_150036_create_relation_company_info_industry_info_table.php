<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relation_company_info_industry_info', function (Blueprint $table) {
            $table->id();
            $table->integer('industry_code')->index();
            $table->integer('company_info_id')->index();

             // ✅ Index đơn (đã có trước đó)
            $table->index('industry_code', 'idx_industry_code');
            $table->index('company_info_id', 'idx_company_info_id');

            // ✅ Index kết hợp (2 chiều)
            $table->index(['company_info_id', 'industry_code'], 'from_company_info_id_to_industry_code_index');
            $table->index(['industry_code', 'company_info_id'], 'from_industry_code_to_company_info_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('relation_company_info_industry_info');
    }
};
