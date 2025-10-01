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
        Schema::create('industry_info', function (Blueprint $table) {
            $table->id(); // Khóa chính tự động tăng
            $table->string('code', 5)->unique(); // Mã ngành (tối đa 5 ký tự)
            $table->string('description', 255); // Mô tả ngành
            $table->integer('level'); // Cấp độ ngành (1, 2, 3, 4, 5)
            $table->string('parent_code', 5)->nullable(); // Mã ngành cha
            // $table->foreign('parent_code')->references('code')->on('industry_info')->onDelete('restrict'); // Khóa ngoại
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('industry_info');
    }
};
