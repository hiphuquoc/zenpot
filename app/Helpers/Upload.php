<?php

namespace App\Helpers;

use Intervention\Image\ImageManagerStatic;
use App\Models\SystemFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Http\UploadedFile;

class Upload {
    
    public static function uploadCustom($requestImage, $name = null){
        $result             = null;
        if(!empty($requestImage)){
            // ===== folder upload
            $folderUpload   = config('image.folder_upload');
            // ===== image upload
            $image          = $requestImage;
            $extension      = config('image.extension');
            // ===== set filename & checkexists
            $name           = $name ?? time();
            $filename       = $name.'-'.time().'.'.$extension;
            $fileUrl        = $folderUpload.$filename;
            // save image resize
            ImageManagerStatic::make($image->getRealPath())
                ->encode($extension, config('image.quality'))
                ->save(Storage::path($fileUrl));
            $result         = $fileUrl;
        }
        return $result;
    }

    
    public static function uploadWallpaperByUrl($imageUrl, $filename, $folderUpload) {
        try {
            // Lấy phần mở rộng file
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filenameWithExt = $filename . '.' . $extension;

            // Tải ảnh về file tạm
            $imageContent = file_get_contents($imageUrl);
            $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('img_') . '.' . $extension;
            file_put_contents($tmpFilePath, $imageContent);

            // Tạo đối tượng UploadedFile
            $requestImage = new UploadedFile(
                $tmpFilePath,
                $filenameWithExt,
                null,
                null,
                true
            );

            // Gọi hàm upload chính
            $uploadedUrl = self::uploadWallpaper($requestImage, $filenameWithExt, $folderUpload);

            // Xoá file tạm
            FileFacade::delete($tmpFilePath);

            return $uploadedUrl;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function uploadWallpaper($requestImage, $filenameWithExt, $folderUpload){
        $result = null;
        if (!empty($requestImage)) {
            // ===== folder upload
            $image              = $requestImage;
            // ===== set filename & checkexists
            $filenameNotExtension = pathinfo($filenameWithExt)['filename'];
            $extension          = pathinfo($filenameWithExt)['extension'];
            $fileUrl            = $folderUpload . $filenameWithExt;
            $gcsDisk            = Storage::disk('gcs');
            // Resize and save the main image
            $imageTmp           = ImageManagerStatic::make($image->getRealPath());
            $percentPixel       = $imageTmp->width() / $imageTmp->height();
            $widthImage         = $imageTmp->width();
            $heightImage        = $imageTmp->height();
            $gcsDisk->put($fileUrl, $imageTmp->encode($extension, config('image.quality'))->resize($widthImage, $heightImage)->stream());
            $result             = $fileUrl;
            // Resize and save the large image
            $fileUrlLarge       = $folderUpload . $filenameNotExtension . '-large.' . $extension;
            $widthImageLarge    = config('image.resize_large_width');
            $heightImageLarge   = $widthImageLarge / $percentPixel;
            $gcsDisk->put($fileUrlLarge, $imageTmp->encode($extension, config('image.quality'))->resize($widthImageLarge, $heightImageLarge)->stream());
            // Resize and save the small image
            $fileUrlSmall       = $folderUpload . $filenameNotExtension . '-small.' . $extension;
            $widthImageSmall    = config('image.resize_small_width');
            $heightImageSmall   = $widthImageSmall / $percentPixel;
            $gcsDisk->put($fileUrlSmall, $imageTmp->encode($extension, config('image.quality'))->resize($widthImageSmall, $heightImageSmall)->stream());
            // Resize and save the mini image
            $fileUrlMini        = $folderUpload . $filenameNotExtension . '-mini.' . $extension;
            $widthImageMini     = config('image.resize_mini_width');
            $heightImageMini    = $widthImageMini / $percentPixel;
            $gcsDisk->put($fileUrlMini, $imageTmp->encode($extension, config('image.quality'))->resize($widthImageMini, $heightImageMini)->stream());
        }
        return $result;
    }

    public static function deleteWallpaper($urlCloud){
        $flag   = false;
        if(!empty($urlCloud)){
            $tmp = pathinfo($urlCloud);
            $filename = $tmp['filename'];
            $extension = $tmp['extension'];
            $foldername = $tmp['dirname'];
            /* xóa wallpaper trong google_cloud_storage */
            Storage::disk('gcs')->delete($urlCloud);
            /* xóa wallpaper Large trong google_cloud_storage */
            Storage::disk('gcs')->delete($foldername.'/'.$filename.'-large.'.$extension);
            /* xóa wallpaper Small trong google_cloud_storage */
            Storage::disk('gcs')->delete($foldername.'/'.$filename.'-small.'.$extension);
            /* xóa wallpaper Mini trong google_cloud_storage */
            Storage::disk('gcs')->delete($foldername.'/'.$filename.'-mini.'.$extension);
            $flag = true;
        }
        return $flag;
    }

    public static function uploadAvatar($requestImage, $filename, $folderUpload){
        $result = null;
        if (!empty($requestImage)) {
            // ===== folder upload
            $image              = $requestImage;
            // ===== set filename & checkexists
            $extension          = pathinfo($filename)['extension'];
            $fileUrl            = $folderUpload . $filename;
            $gcsDisk            = Storage::disk('gcs');
            // Resize and save the main image
            $imageTmp           = ImageManagerStatic::make($image->getRealPath());
            $widthImage         = $imageTmp->width();
            $heightImage        = $imageTmp->height();
            $gcsDisk->put($fileUrl, $imageTmp->encode($extension, config('image.quality'))->resize($widthImage, $heightImage)->stream());
            $result             = $fileUrl;
        }
        return $result;
    }

    public static function uploadFile($requestFile, $filename, $folderUpload){
        $result = null;
        if (!empty($requestFile)) {
            $extension = $requestFile->getClientOriginalExtension();
            $filenameWithExt = $filename . '.' . $extension;
            $fileUrl = $folderUpload . $filenameWithExt;

            // Upload to GCS (or any other configured disk)
            $gcsDisk = Storage::disk('gcs');
            $gcsDisk->put($fileUrl, file_get_contents($requestFile));

            $result = $fileUrl;
        }
        return $result;
    }
    
}