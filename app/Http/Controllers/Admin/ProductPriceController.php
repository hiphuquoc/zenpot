<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductPrice;
use App\Models\SystemFile;

class ProductPriceController extends Controller {

    public function loadImageForProductPrice(Request $request){
        $response       = '';
        $idProductPrice = $request->get('product_price_id') ?? 0;
        $prefixNameInput     = $request->get('prefix_name_input') ?? '';
        if(!empty($idProductPrice)){
            $files      = SystemFile::select('*')
                            ->where('attachment_id', $idProductPrice)
                            ->where('relation_table', 'product_price')
                            ->get();
            foreach($files as $file){
                $imageUrlSmall = !empty($file->file_path) ? \App\Helpers\Image::getUrlImageSmallByUrlImage($file->file_path) : config('image.default');
                $response .= '<div class="imageProductPrice_item">
                                        <img id="imageUpload" src="'.$imageUrlSmall.'" />
                                        <input type="hidden" name="'.$prefixNameInput.'[product_price_file_uploaded][]" value="'.$file->id.'" />
                                        <div class="imageProductPrice_item_removeIcon">
                                            <i class="fa-solid fa-xmark"></i>
                                        </div>
                                    </div>';
            }
        }
        return $response;
    }
}
