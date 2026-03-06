<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseNotification
{
    public static function sendNotification($receiver, $title, $body, $url = null)
    {
        // Handle both User and Employee types
        $token = $receiver->fcm_token;

        if (!$token) {
            return false; // No device token
        }

        $SERVER_API_KEY = config('services.fcm.server_key'); // Store in config/services.php

        $data = [
            "to" => $token,
            "notification" => [
                "title" => $title,
                "body"  => $body,
                "sound" => "default",
            ],
            "data" => [
                "url" => $url,
            ],
        ];

        $response = Http::withHeaders([
            "Authorization" => "key={$SERVER_API_KEY}",
            "Content-Type"  => "application/json",
        ])->post('https://fcm.googleapis.com/fcm/send', $data);

        return $response->successful();
    }
}
