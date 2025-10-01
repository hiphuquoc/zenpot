<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use MeiliSearch\Client;

class ReindexSearchDataPost extends Command {

    protected $signature    = 'search:reindex-post';
    protected $description  = 'Re-index all Posts into Meilisearch';

    public function handle() {
        $this->info('⚙️ Đang cấu hình Meilisearch index cho Post...');

        // Cấu hình Meilisearch index
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
        $index = $client->index(config('scout.prefix') . 'post_info'); // <- tên index chính xác trên Meilisearch Cloud

        // Cập nhật các field có thể search được
        $index->updateSearchableAttributes([
            'title',
            'seos',           // các title từ seos.infoSeo.title
            'exchangeTagsTitles', // các title từ exchangeTags.infoExchangeTag.seos.infoSeo.title
        ]);

        $this->info('✅ Đã cấu hình searchable attributes cho index "post_info".');

        // Bắt đầu reindex dữ liệu
        $this->info('⏳ Bắt đầu re-index toàn bộ Posts...');

        Post::with([
                'seo',
                'seos.infoSeo',
                'exchangeTags.infoExchangeTag.seos.infoSeo'
            ])
            ->chunk(50, function ($posts) {
                $posts->each->searchable(); // Laravel Scout xử lý
                $this->info('✅ Đã index thêm ' . $posts->count() . ' posts');
            });

        $this->info('🎉 Hoàn tất re-index!');
    }

}