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
        Schema::create('product_info_translate', function (Blueprint $table) {
            $table->id();
            $table->integer('product_info_id');
            $table->string('language', 3);
            $table->text('material')->nullable();   // Chất liệu
            $table->text('usage')->nullable();        // Ứng dụng
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('product_info_translate');
    }
};
