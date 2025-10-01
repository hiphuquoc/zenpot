<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Controllers\Admin\ChatGptController;
use App\Http\Controllers\Admin\PostController;

use App\Models\Seo;
use App\Models\Post;
use App\Models\Exchange;
use App\Models\ExchangeTag;
use App\Models\Crawl;
use App\Models\SystemFile;

use App\Helpers\Charactor;
use App\Helpers\Upload;

use Symfony\Component\Mime\MimeTypes;
use App\Http\Requests\PostRequest;

class UploadPostCrawl implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $idCrawl;
    public  $tries = 2; // Số lần thử lại

    public function __construct($idCrawl){
        $this->idCrawl    = $idCrawl;
    }

    public function handle(){
        try {
            
            $infoCrawl          = Crawl::find($this->idCrawl);

            // check xem đã đăng tin chưa
            if(!empty($infoCrawl->status)) {
                echo 'Tin này đã được đăng trước đó';
                return false;
            }
        
            // convert dữ liệu trước khi upload
            $dataUpload         = self::buildDataToUpload($infoCrawl);
            
            // trường hợp không có sdt dừng
            if(empty($dataUpload['contact_phone'])){
                Crawl::updateItem($infoCrawl->id, [
                    'notes'   => 'Không lọc được số điện thoại!',
                ]);
                return false;
            }

            // tiến hành upload => trả về idPost
            dispatch(function () use ($dataUpload, $infoCrawl) {
                // truyền thêm biến đánh dấu để return idPost
                $dataUpload['crawl'] = true;

                $request    = PostRequest::create(route('admin.post.view'), 'POST', $dataUpload);
                $request->setLaravelSession(session());
                
                $controller = app(PostController::class);
                $idPost     = $controller->createAndUpdate($request);

                if(!empty($idPost)){
                    // lấy thông SEO
                    $infoPost    = Post::select('*')
                                    ->where('id', $idPost)
                                    ->with('seo')
                                    ->first();
                    // cập nhật lại trạng thái và đường dẫn
                    $infoCrawl->status      = 1;
                    $infoCrawl->slug_full   = $infoPost->seo->slug_full;
                    $infoCrawl->save();
                    // upload hình ảnh vào google cloud -> trả về array đường dẫn làm gallery
                    $imageUrlsOnCloud   = [];
                    $prefixNameImage    = Charactor::convertStrToUrl($infoCrawl->title) . '-' . time() . '-';
                    $i                  = 1;
                    $folderUpload       = config('main_' . env('APP_NAME') . '.google_cloud_storage.upload');

                    foreach (json_decode($infoCrawl->image_urls, true) as $imageUrl) {
                        $filenameNonExt     = $prefixNameImage . $i;
                        $uploadedUrl        = Upload::uploadWallpaperByUrl($imageUrl, $filenameNonExt, $folderUpload);
                        if ($uploadedUrl) {
                            $imageUrlsOnCloud[] = $uploadedUrl;
                        }
                        ++$i;
                    }
                    // cập nhật gallery
                    $i  = 0;
                    foreach ($imageUrlsOnCloud as $image) {
                        if($i==0){
                            // lấy ảnh đầu tiên làm ảnh đại diện
                            $infoPost->seo->image = $image;
                            $infoPost->seo->save();
                        }

                        // Lấy tên file từ đường dẫn
                        $fileName = basename($image); // xi-nghiep-phan-phoi-khi-thap-ap-vung-tau-thong-bao-moi-thau-1749758926-1.webp.jpg

                        // Tách tên file và phần mở rộng
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // jpg
                        $fileNameNonExtesion = pathinfo($fileName, PATHINFO_FILENAME); // xi-nghiep-phan-phoi-khi-thap-ap-vung-tau-thong-bao-moi-thau-1749758926-1.webp

                        // Xác định MIME type dựa trên phần mở rộng
                        $mimeDetector   = new MimeTypes();
                        $fileType       = $mimeDetector->getMimeTypes($fileExtension)[0] ?? 'application/octet-stream';

                        // Insert vào bảng system_files
                        SystemFile::insertItem([
                            'attachment_id'         => $idPost,
                            'relation_table'        => 'post_info',
                            'file_name'             => $fileNameNonExtesion,
                            'file_path'             => $image,
                            'file_extension'        => $fileExtension,
                            'file_type'             => $fileType,
                        ]);

                        ++$i;
                    }

                }
            });

        } catch (\Exception $e) {
            throw $e; // Đẩy lại lỗi để Laravel tự động thử lại
        }
    }

    public static function buildDataToUpload($infoCrawl){ // nhận vào collection
        $title              = $infoCrawl->title;
        $postNameDefault    = config('main_'.env('APP_NAME').'.post_name_default');
        /* =============== mặc định ================ */
        $responseData = [];
        $responseData['language']                         = 'vi';
        $responseData['type']                             = 'copy';
        $responseData['status']                           = 1; // hiển thị
        $responseData['type_vip']                         = 0; // tin thường
        $responseData['contact_name']                     = $infoCrawl->contact_name ?? 'Người đăng';
        $responseData['contact_position']                 = config('main_'.env('APP_NAME').'.post_name_default');
        $responseData['contents'][0]['content_title']     = 'Nội dung tin đăng';
        $responseData['contents'][0]['content_sub_title'] = 'Thông tin nội dung tin đăng';
        $responseData['contents'][0]['content_icon']      = 'icon_diagram_project';
        $responseData['contents'][0]['content_ordering']  = 1;
        $responseData['rating_aggregate_count']           = rand(50, 500);
        $responseData['rating_aggregate_star']            = '4.'.rand(4, 8);
        /*    =============== xử lý CODE ================ */
        $responseData['slug']   = Charactor::convertStrToUrl($title).'-'.Charactor::randomString(5);
        $infoParent             = Seo::select('id')
                                        ->where('slug', 'san-hop-tac-kinh-doanh')
                                        ->first();
        $responseData['parent'] = $infoParent->id ?? '';
        // so sánh với tag quy mô (khu vực) để chọn làm tag nổi bật
        $responseData['exchangeOutstandings'] = [];
        if(!empty($infoCrawl->location)){
            $location           = self::removeCharactorInLocation($infoCrawl->location);
            $exchangesScale     = ExchangeTag::select('*')
                                    ->where('type_filter', 'scale')
                                    ->get();
            foreach($exchangesScale as $e){
                $titleConver    = self::removeCharactorInLocation($e->seo->title);                
                if($location==$titleConver){
                    $responseData['exchangeOutstandings'] = [$e->id];
                    break;
                }
            }
        }
        /*     =============== craw giữ nguyên ================ */
        $responseData['title']        = $title;
        $responseData['contact_name'] = $infoCrawl->contact_name ?? $postNameDefault;
        /*   =============== xử lý AI ================ */
        $exchanges          = Exchange::select('*')
                                ->whereHas('seo', function($query){
                                    $query->where('level', '>', 1);
                                })
                                ->get();
        $exchangesIndustry  = ExchangeTag::select('*')
                                ->where('type_filter', 'industry')
                                ->get();
        $arrayIndustry      = []; // key là exchange_tag_id => value là title bên bảng seo
        foreach($exchangesIndustry as $e) $arrayIndustry[$e->id] = $e->seo->title;
        
        $promptText         = 'tôi có những thông tin sau:
                                - Tiêu đề: "'.$infoCrawl->title.'"
                                - Nội dung: "'.$infoCrawl->content.'"
                                - Phone liên hệ: "'.$infoCrawl->contact_phone.'"
                                - Array json category danh mục để đối chiếu đánh category: ['.json_encode($exchanges).']
                                - Array json tag nghành nghề để đối chiếu và đánh tag: ['.json_encode($arrayIndustry).']

                                tôi cần bạn xây dựng và hoàn thiện giúp tôi thành mảng có cấu trúc như sau - tôi sẽ note và hướng dẫn bạn trong từng key bên dưới:
                                "title" => "",                      // định dạng tiêu đề lại thành chữ Hoa thường hợp lí giúp tôi (chứ không phải in hoa hết)
                                "exchanges" => [id_1, id_2,...],    // là các id của danh mục phù hợp với nội dung bài đăng, bạn đối chiếu với array category tôi cung cấp
                                "exchangeTags" => [id_1, id_2,...], // là các id của nghành nghề phù hợp với nội dung bài đăng, bạn đối chiếu với array tag nghành nghề tôi cung cấp
                                "seo_title" => "",                  // dựa vào tiêu đề và nội dung bài viết bạn viết giúp tôi 1 đoạn meta_title chuẩn SEO, tối đa 60 ký tự, thu hút
                                "seo_description" => "",            // dựa vào tiêu đề và nội dung bài viết bạn viết giúp tôi 1 đoạn meta_description chuẩn SEO, tối đa 160 ký tự, thu hút
                                "content" => "",                    // là nội dung bạn viết lại dựa trên nội dung bài đăng, loại bỏ các thẻ html thừa trong nội dung gốc, viết và trình bày lại nội dung cho dễ đọc, rõ nghĩa và hay hơn như một bài đăng trên social để thu hút người đọc. trình bày trong các thẻ <h3>, <p>, <ul>, <li>, <a>,... phù hợp. Lưu ý quan trọng nếu có thẻ a phải đặt rel="nofollow"
                                "contact_phone" => "",              // là phone liên hệ tôi cung cấp ở trên, bạn kiểm tra xem đủ chưa vì đôi khi bị thiếu, nếu chưa đủ tìm số điện thoại đầy đủ trong nội dung để thêm vào, nếu tìm không có thì xem trong nội dung có số điện thoại thay thế không, định dạng chỉ giữ số viết liền
                                "contact_zalo" => "",               // là số zalo bạn lọc được trong nội dung của bài đăng - nếu không có hãy lấy số điện thoại hoàn thiện ở trên
                                "contact_email" => "",              // là email liên hệ bạn lọc được trong nội dung của bài đăng - nếu không có cứ bỏ trống

                                yêu cầu trả về kết quả là một array json chuẩn, chỉ cần như vậy không cần ghi chú hay giải thích gì thêm';
        // gọi AI
        $infoPrompt             = [];
        $infoPrompt['version']  = 'qwen-plus';
        $responseFromAI         = ChatGptController::callApi($promptText, $infoPrompt);
        $arrayDataFromAi        = self::parseApiContent($responseFromAI['content']);
        // gộp với mảng trước
        $arrayDataFromAi        = $arrayDataFromAi[0] ?? $arrayDataFromAi; // trường hợp bị đưa vào 1 tầng nữa
        foreach($arrayDataFromAi as $key => $value) {
            if($key!='content'){
                $responseData[$key] = $value;
            }else {
                $responseData['contents'][0]['content'] = $value;
            }
        }
        
        return $responseData;
    }

    /* helpers */
    private static function removeCharactorInLocation(string $location){
        $tmp        = explode(',', $location);
        // tách quận huyện => chuyển thành ký tự in thường
        $location   = strtolower(trim(end($tmp)));
        // loại bỏ dấu .
        $location   = str_replace('.', '', $location);
        // loại bỏ dấu cách
        $location   = str_replace(' ', '', $location);
        return $location;
    }

    // Hàm xử lý và lấy mảng từ content
    private static function parseApiContent($content) {

        // Chuyển đổi chuỗi JSON thành mảng PHP
        $parsedData = json_decode($content, true);

        // Kiểm tra lỗi khi phân tích JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        // Trả về mảng đã phân tích
        return $parsedData;
    }

}
