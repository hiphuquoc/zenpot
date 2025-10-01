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
        Schema::create('commune_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Thông tin liên kết
            $table->unsignedInteger('province_info_id')->index();    // khóa tới province_info.id
            $table->string('province_code', 6)->nullable()->index(); // Mã ISO của tỉnh (dự phòng)
            
            // Thông tin cơ bản
            $table->string('name', 100);        // tên phường/xã/thị trấn
            $table->string('type', 20);         // loại: ward / commune / township
            $table->string('postcode', 6)->nullable();        // mã bưu chính đầy đủ
            
            // Dữ liệu bổ sung
            $table->string('area_km2', 8)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->point('location')->nullable();             // nếu có kinh/vĩ độ
            
            // Ghi chú
            $table->text('notes')->nullable();                 // ghi chú nếu cần
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('commune_info');
    }
};
