<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AppsScriptChannel
{
    public function send($notifiable, $notification): void
    {
        $endpoint = config('services.google_apps_script.url');
        $secret = config('services.google_apps_script.secret');

        if (!$endpoint || !$secret) {
            throw new RuntimeException(
                'Konfigurasi Google Apps Script belum lengkap.'
            );
        }

        $data = $notification->toAppsScript($notifiable);

        $response = Http::timeout(30)
            ->acceptJson()
            ->post($endpoint, array_merge($data, [
                'token' => $secret,
            ]));

        $result = $response->json();

        if (
            !$response->successful() ||
            !is_array($result) ||
            ($result['success'] ?? false) !== true
        ) {
            $message = is_array($result)
                ? ($result['message'] ?? 'Respons Google tidak valid.')
                : 'Google tidak mengembalikan respons JSON yang valid.';

            Log::error('Apps Script mail error', [
                'http_status' => $response->status(),
                'message' => $message,
            ]);

            throw new RuntimeException(
                'Apps Script: ' . $message
            );
        }
    }
}
