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
        Schema::create('post_attachment', function (Blueprint $table) {
            $table->id();
            $table->integer('seo_id'); /* kết nối với seo_id vì mỗi ngôn ngữ sẽ có bản đính kèm theo ngôn ngữ riêng */
            $table->text('title');
            $table->text('file_name');
            $table->text('file_extension');
            $table->text('file_type')->nullable();
            $table->text('file_cloud');
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
        // Schema::dropIfExists('post_attachment');
    }
};
