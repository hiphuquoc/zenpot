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
        Schema::create('province_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->string('code', 6)->nullable()->index();           // ISO 3166-2
            $table->string('postcode_prefix', 20)->nullable();       // ví dụ "30xxxx"
            $table->text('merged_from')->nullable();
            $table->string('admin_center', 100)->nullable();
            $table->float('area_km2', 8, 2)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->date('effective_date')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('province_info');
    }
};
