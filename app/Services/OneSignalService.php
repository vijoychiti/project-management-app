<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $apiKey;

    public function __construct()
    {
        $this->appId = env('ONESIGNAL_APP_ID');
        $this->apiKey = env('ONESIGNAL_REST_API_KEY');
    }

    /**
     * Send a notification to specific users via External User ID.
     *
     * @param array $userIds Array of user IDs (will be cast to strings)
     * @param string $title
     * @param string $message
     * @param string|null $url Optional URL to open
     * @return void
     */
    public function sendNotification(array $userIds, string $title, string $message, string $url = null)
    {
        if (empty($this->appId) || empty($this->apiKey)) {
            Log::warning('OneSignal keys not configured.');
            return;
        }

        if (empty($userIds)) {
            return;
        }

        // Convert user IDs to strings
        $userIds = array_map('strval', $userIds);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $this->appId,
                'include_external_user_ids' => $userIds,
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
                'url' => $url,
            ]);

            if ($response->failed()) {
                Log::error('OneSignal Notification Failed: ' . $response->body());
            } 
        } catch (\Exception $e) {
            Log::error('OneSignal Exception: ' . $e->getMessage());
        }
    }
}
