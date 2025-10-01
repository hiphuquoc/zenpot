<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UploadPostCrawl;
use App\Models\Crawl;

class UploadPostFail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 're-upload:post_info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload lại các bài crawl upload fail';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $crawls     = Crawl::select('*')
                        ->where('status', 0)
                        ->orderBy('id', 'DESC')
                        ->get();
        foreach($crawls as $crawl){
            UploadPostCrawl::dispatch($crawl->id);
        }
    }
}