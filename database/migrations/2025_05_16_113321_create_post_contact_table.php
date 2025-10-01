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
        Schema::create('post_contact', function (Blueprint $table) {
            $table->id();
            $table->integer('post_info_id');
            $table->text('avatar_file_cloud')->nullable();
            $table->text('name');
            $table->text('position');
            $table->text('phone');
            $table->text('zalo')->nullable();
            $table->text('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('post_contact');
    }
};
