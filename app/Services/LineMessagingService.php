<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    protected string $channelAccessToken;

    protected string $channelSecret;

    protected ?string $addFriendUrl;

    public function __construct()
    {
        $this->channelAccessToken = config('services.line_messaging.channel_access_token') ?? '';
        $this->channelSecret = config('services.line_messaging.channel_secret') ?? '';
        $this->addFriendUrl = config('services.line_messaging.add_friend_url') ?: null;
    }

    /**
     * Send a push message to a LINE user (for class reminders, etc.).
     */
    public function sendPushMessage(string $lineUserId, string $message): bool
    {
        $response = Http::withToken($this->channelAccessToken)
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    ['type' => 'text', 'text' => $message],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('LINE Messaging API push failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Reply to a webhook event (e.g. when user sends a message).
     */
    public function reply(string $replyToken, string $message): bool
    {
        $response = Http::withToken($this->channelAccessToken)
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [
                    ['type' => 'text', 'text' => $message],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('LINE Messaging API reply failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Verify webhook signature from LINE.
     */
    public function validateSignature(string $body, string $signature): bool
    {
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);

        return hash_equals(base64_encode($hash), $signature);
    }

    public function getAddFriendUrl(): ?string
    {
        return $this->addFriendUrl;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->channelAccessToken) && ! empty($this->channelSecret);
    }
}
