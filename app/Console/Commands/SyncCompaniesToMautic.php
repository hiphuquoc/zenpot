<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SyncCompaniesToMautic extends Command
{
    protected $signature = 'mautic:sync-companies {--limit=100}';
    protected $description = 'Đồng bộ công ty từ bảng Company vào Mautic';

    protected Client $client;
    protected string $baseUrl;
    protected string $segmentId;

    public function __construct()
    {
        parent::__construct();

        $this->baseUrl = rtrim(env('MAUTIC_URL', 'https://mail.hoptackinhdoanh.com'), '/');
        $this->segmentId = env('MAUTIC_SEGMENT_ID', 1);

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'auth' => [env('MAUTIC_USERNAME'), env('MAUTIC_PASSWORD')],
            'timeout' => 30,
        ]);
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');

        // $companies = Company::whereNotNull('email')
        //     ->where('email', '!=', '')
        //     ->whereNull('mautic_contact_id')
        //     ->take($limit)
        //     ->get();
        $companies = Company::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('tax_code', '1702204052')
            ->whereNull('mautic_contact_id')
            ->take($limit)
            ->get();

        if ($companies->isEmpty()) {
            $this->info('Không có công ty mới để đồng bộ.');
            return;
        }

        $this->info("Đang đồng bộ {$companies->count()} công ty...");

        foreach ($companies as $company) {
            $this->syncCompany($company);
        }

        $this->info('Đồng bộ hoàn tất!');
    }

    private function syncCompany(Company $company): void
    {
        try {
            $contactId = $this->findOrCreateContact($company);

            if ($contactId) {
                $company->update(['mautic_contact_id' => $contactId]);
                $this->addContactToSegment($contactId);
            }
        } catch (\Throwable $e) {
            Log::error("Lỗi sync công ty {$company->email}: {$e->getMessage()}");
        }
    }

    private function findOrCreateContact(Company $company): ?int
    {
        // 1. Tìm contact theo email
        $response = $this->request('GET', '/api/contacts', [
            'query' => ['search' => 'email:' . $company->email]
        ]);

        if (!empty($response['contacts'])) {
            return array_key_first($response['contacts']);
        }

        // 2. Tạo contact mới
        $data = [
            'firstname' => $company->legal_representative ?? '',
            'position'  => 'Người đại diện',
            'company'   => mb_substr($company->name, 0, 64), // Giới hạn 64 ký tự
            'email'     => $company->email,
            'phone'     => $company->phone,
            'website'   => $company->website,
            'fullname_company' => $company->name,                               // Custom field chứa tên đầy đủ
            'tax_code' => $company->tax_code,                                   // Custom field chứa MST
            'province_name' => ucwords($company->province_name),                // Custom field chứa Tỉnh thành
            'main_industry_text' => $company->main_industry_text,               // Custom field chứa Nghành nghề chính
            'slug_full' => 'https://hoptackinhdoanh.com/'.$company->seo->slug_full,  // Custom field chứa Đường dẫn về hồ sơ
        ];

        $response = $this->request('POST', '/api/contacts/new', ['form_params' => $data]);

        if (isset($response['contact']['id'])) {
            return $response['contact']['id'];
        }

        Log::error("Không thể tạo contact: " . json_encode($response));
        return null;
    }

    private function addContactToSegment(int $contactId): void
    {
        $endpoint = "/api/segments/{$this->segmentId}/contact/{$contactId}/add";
        $response = $this->request('POST', $endpoint);

        if (!isset($response['success']) || !$response['success']) {
            Log::error("Không thể thêm contact ID {$contactId} vào segment {$this->segmentId}: " . json_encode($response));
        }
    }

    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            $res = $this->client->request($method, $uri, $options);
            $json = json_decode($res->getBody()->getContents(), true);
            return $json ?? [];
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody()->getContents();
                $msg .= ' | Response: ' . $body;
            }
            Log::error("Lỗi request Mautic: {$msg}");
            return [];
        }
    }
}
