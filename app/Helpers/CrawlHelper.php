<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class CrawlHelper
{

    public static function getHtml($url, $proxy = true){
        // Thử Guzzle trước
        $result = self::fetchHtml($url, [
            'timeout' => 60,
        ], $proxy);
        if(!empty($result['html'])) return $result['html'];

        // nếu không lấy được thử với node js
        if(empty($result['html'])){
             // hàm fetch bằng node js
            $escapedUrl = escapeshellarg($url);
            $scriptPath = base_path('scripts/crawlDefault.cjs');
            $output = shell_exec("node {$scriptPath} {$escapedUrl} 2>&1");
            $result = json_decode($output, true);

            if(!empty($result['html'])){
                return $result['html'];
            }else {
                return '';
            } 
        }
        return '';
    }

    public static function getMultiHtmlByNodeJS($urls)
    {
        if (!is_array($urls) || empty($urls)) {
            Log::error('Danh sách URL không hợp lệ hoặc rỗng', ['urls' => $urls]);
            return [];
        }

        // Tạo file tạm chứa danh sách URL đầu vào
        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $inputFile = tempnam($tempDir, 'crawl_input_');
        chmod($inputFile, 0664);
        file_put_contents($inputFile, json_encode(['urls' => $urls]));

        $outputFile = tempnam($tempDir, 'crawl_output_');
        chmod($outputFile, 0664);

        $scriptPath = base_path('scripts/crawlMulti.cjs');
        if (!file_exists($scriptPath)) {
            Log::error('File script Node.js không tồn tại', ['script_path' => $scriptPath]);
            return [];
        }

        // Chạy script Node.js
        $command = "node {$scriptPath} " . escapeshellarg($inputFile) . " " . escapeshellarg($outputFile) . " 2>&1";
        $output = shell_exec($command);

        if (!file_exists($outputFile)) {
            Log::error('Không tìm thấy file kết quả sau khi chạy script', [
                'output_file' => $outputFile,
                'shell_output' => $output,
            ]);
            @unlink($inputFile);
            return [];
        }

        $jsonContent = file_get_contents($outputFile);
        @unlink($inputFile);
        @unlink($outputFile);

        if (empty($jsonContent)) {
            Log::error('File kết quả rỗng sau khi chạy script Node.js');
            return [];
        }

        $results = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Lỗi parse JSON từ file kết quả', [
                'json_error' => json_last_error_msg(),
                'content_sample' => substr($jsonContent, 0, 500),
            ]);
            return [];
        }

        if (!is_array($results)) {
            Log::error('Kết quả sau parse JSON không phải mảng', ['type' => gettype($results)]);
            return [];
        }

        if (!isset($results[0])) {
            $results = [$results];
        }

        $htmlResults = [];
        foreach ($results as $result) {
            if (!isset($result['url'])) {
                continue;
            }

            $url = $result['url'];
            $html = $result['html'] ?? '';
            $htmlResults[$url] = $html;
        }

        return $htmlResults;
    }

    public static function fetchHtml(string $url, array $options = [], bool $proxy = true): array
    {
        // Kiểm tra URL hợp lệ
        if (empty($url)) {
            return ['error' => 'URL không được để trống'];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error' => 'URL không hợp lệ'];
        }

        // Cấu hình proxy từ .env
        $proxyUrl = sprintf(
            'http://%s:%s@%s:%s',
            config('services.proxy.username', ''),
            config('services.proxy.password', ''),
            config('services.proxy.host', 'pr.oxylabs.io'),
            config('services.proxy.port', '7777')
        );

        // Cấu hình mặc định
        $defaultOptions = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'vi-VN,vi;q=0.9',
                'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST),
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ],
            'timeout' => 30,
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
                'track_redirects' => true,
            ],
            'verify' => true,
            'cookies' => new CookieJar(),
        ];

        // Thêm proxy nếu được bật
        if ($proxy) {
            $defaultOptions['proxy'] = $proxyUrl;
        }

        // Gộp tùy chọn, ưu tiên $options của người dùng
        $options = array_replace_recursive($defaultOptions, $options);

        // Đảm bảo timeout là số
        if (isset($options['timeout']) && !is_numeric($options['timeout'])) {
            $options['timeout'] = $defaultOptions['timeout'];
        }

        try {
            // Khởi tạo Guzzle client
            $client = new Client();

            // Gửi GET request
            $response = $client->get($url, $options);

            // Kiểm tra mã trạng thái HTTP
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return ['error' => "Request thất bại với mã trạng thái: $statusCode"];
            }

            // Lấy nội dung HTML
            $html = $response->getBody()->getContents();

            // Kiểm tra nội dung rỗng
            if (empty($html)) {
                return ['error' => 'Không thể lấy nội dung HTML từ URL'];
            }

            // Lấy lịch sử redirect và final URL
            $redirectHistory = $response->getHeader('X-Guzzle-Redirect-History') ?? [];
            $finalUrl = !empty($redirectHistory) ? end($redirectHistory) : $url;

            // Trả về HTML và thông tin debug
            return [
                'html' => $html,
                'final_url' => $finalUrl,
                'status_code' => $statusCode,
                'redirect_history' => $redirectHistory,
            ];

        } catch (RequestException $e) {
            // Ghi log lỗi Guzzle nghiêm trọng
            $errorMessage = $e->getMessage();
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            Log::error('Lỗi nghiêm trọng khi crawl HTML với Guzzle', [
                'url' => $url,
                'error_message' => $errorMessage,
                'status_code' => $statusCode,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return ['error' => 'Lỗi crawl HTML: ' . $errorMessage];

        } catch (\Exception $e) {
            // Ghi log lỗi không xác định
            Log::error('Lỗi không xác định khi crawl HTML', [
                'url' => $url,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return ['error' => 'Lỗi không xác định: ' . $e->getMessage()];
        }
    }
}