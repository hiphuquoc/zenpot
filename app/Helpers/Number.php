<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Number {

    public static function calculatorSaleOffPercent($priceOrigin, $priceSell) {
        $result = 0;
        if (!empty($priceOrigin) && !empty($priceSell) && $priceOrigin > 0) {
            $result = (($priceOrigin - $priceSell) / $priceOrigin) * 100;
        }

        // Nếu không giảm (hoặc giá tăng), vẫn hiển thị dấu đúng logic
        $formatted = ($result >= 0 ? '-' : '+') . abs(round($result, 0)) . '%';
        return $formatted;
    }


    // public static function getFormatPriceByLanguage($number, $language, $showCurrency = true){
    //     $result         = null;
    //     $tmp            = self::getPriceByLanguage($number, $language);
    //     if($showCurrency==true){
    //         $result     = $tmp['number'].$tmp['currency_code'];
    //     }else {
    //         $result     = $tmp['number'];
    //     }
    //     return $result;
    // }

    public static function getFormatPriceByLanguage($number, $language, $showCurrency = true){
        $result = null;
        $tmp = self::getPriceByLanguage($number, $language);

        // Format số với số chữ số thập phân thích hợp
        $formattedNumber = number_format($tmp['number'], $tmp['decimal_places'], '.', ',');
        
        if ($showCurrency) {
            $result = $formattedNumber . $tmp['currency_code'];
        } else {
            $result = $formattedNumber;
        }
        
        return $result;
    }

    public static function getPriceByLanguage($number, $language){
        /* ghi chú: ở hàm này không xử lý việc */
        $result         = [
            'number'            => 0,
            'currency'          => null,
            'currency_code'     => null,
        ];
        // $exchangeRate               = config('language.'.$language.'.money_value');
        // $calculator                 = $number * $exchangeRate;
        // $result['number']           = $calculator;

        $result['number']           = $number;
        $result['currency']         = config('language.'.$language.'.currency');
        $result['currency_code']    = config('language.'.$language.'.currency_code');
        $result['decimal_places']   = config('language.'.$language.'.decimal_places');
        
        return $result;
    }

    public static function getPriceOriginByCountry($number){
        // /* hệ số giảm giá theo khu vực (nằm trong session) */
        // $percentDiscount            = session()->get('info_gps')['percent_discount']  
        //                                 ?? session()->get('info_timezone')['percent_discount']
                                        // ?? Cache::get('info_timezone')['percent_discount'] 
                                        // ?? Cache::get('info_gps')['percent_discount'] 
        //                                 /* ip chỉ là phương án cuối cùng */
        //                                 ?? session()->get('info_ip')['percent_discount']
        //                                 ?? Cache::get('info_ip')['percent_discount'] 
        //                                 ?? config('main_'.env('APP_NAME').'.percent_discount_default');
        // Get cookies from the request
        $infoGps       = json_decode(request()->cookie('info_gps'), true);
        $infoTimezone  = json_decode(request()->cookie('info_timezone'), true);
        $infoIp        = json_decode(request()->cookie('info_ip'), true);
        // Determine the discount factor from available cookies or fallback
        $percentDiscount            = $infoGps['percent_discount'] 
                                        ?? $infoTimezone['percent_discount'] 
                                        ?? Cache::get('info_timezone')['percent_discount'] 
                                        ?? Cache::get('info_gps')['percent_discount'] 
                                        ?? $infoIp['percent_discount'] 
                                        ?? Cache::get('info_ip')['percent_discount'] 
                                        ?? config('main_' . env('APP_NAME') . '.percent_discount_default');
        /* kết quả */
        $number                     = $number * $percentDiscount;
        return $number;
    }

    public static function calculatorSaleOffByPriceMaxAndPriceOriginByCountry($priceMax, $priceOriginByCountry){
        $saleOff                    = 0;
        if(!empty($priceMax)&&!empty($priceOriginByCountry)){
            $saleOff                = number_format((($priceMax - $priceOriginByCountry)/$priceMax)*100, 0);
        }
        return $saleOff;
    }

    public static function timeAgoVi($datetime) {
        $time = Carbon::parse($datetime);
        $now = Carbon::now();
        $diffInSeconds = $now->diffInSeconds($time);

        if ($diffInSeconds < 60) {
            return 'vài giây trước';
        } elseif ($diffInSeconds < 3600) {
            $minutes = $now->diffInMinutes($time);
            return $minutes . ' phút trước';
        } elseif ($diffInSeconds < 86400) {
            // Dưới 24 giờ → trả về "Hôm nay"
            return 'Hôm nay';
        } elseif ($diffInSeconds < 604800) {
            $days = $now->diffInDays($time);
            return $days . ' ngày trước';
        } elseif ($diffInSeconds < 2592000) {
            $weeks = floor($now->diffInDays($time) / 7);
            $weeks = $weeks === 0 ? 1 : $weeks;
            return $weeks . ' tuần trước';
        } elseif ($diffInSeconds < 31536000) {
            $months = $now->diffInMonths($time);
            return $months . ' tháng trước';
        } else {
            $years = $now->diffInYears($time);
            return $years . ' năm trước';
        }
    }

    public static function formatViews($number) {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        }
        return (string) $number;
    }

    public static function normalizePhoneNumber($phone) {
        // Loại bỏ mọi ký tự không phải số
        return preg_replace('/\D+/', '', $phone);
    }
}