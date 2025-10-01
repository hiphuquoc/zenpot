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
        Schema::create('crawl_info', function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->text('slug_full')->nullable();
            $table->text('title');
            $table->text('location')->nullable();
            $table->text('contact_name')->nullable();
            $table->text('contact_phone')->nullable();
            $table->text('content');
            $table->text('image_urls')->nullable();
            $table->boolean('status')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('crawl_info');
    }
};
