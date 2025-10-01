<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MauticEmailService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl  = config('services.mautic.base_url');
        $this->username = config('services.mautic.username');
        $this->password = config('services.mautic.password');
    }

    public function sendEmail($email, $subject, $html)
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->post($this->baseUrl . '/api/emails/send', [
                'email'   => $email,
                'subject' => $subject,
                'html'    => $html,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Send failed: ' . $response->body());
        }
    }
}
