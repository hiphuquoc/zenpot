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
        Schema::create('seo', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->integer('level');
            $table->integer('parent')->nullable();
            $table->integer('ordering')->nullable();
            $table->integer('topic')->nullable();
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('slug');
            $table->text('slug_full');
            $table->integer('link_canonical')->nullable();
            $table->string('type', 50);
            $table->string('rating_author_name', 1)->nullable();
            $table->string('rating_author_star', 5);
            $table->integer('rating_aggregate_count')->nullable();
            $table->string('rating_aggregate_star', 5)->nullable();
            $table->string('language', 3);
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        // Tạo index thủ công cho các cột text dùng raw SQL
        DB::statement('CREATE INDEX seo_slug_index ON seo (slug(191))');
        DB::statement('CREATE INDEX seo_slug_full_index ON seo (slug_full(191))');
        DB::statement('CREATE UNIQUE INDEX seo_slug_full_unique ON seo (slug_full(191))');
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('seo');
    }
};
