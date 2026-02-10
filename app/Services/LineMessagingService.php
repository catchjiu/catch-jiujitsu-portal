<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    /** Last LINE API error (for CLI/logging when push fails). */
    protected static ?string $lastPushError = null;

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
     * Send a text push message to a LINE user.
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
     * Send a Flex Message push to a LINE user.
     * $contents = Flex bubble container (array, will be JSON-encoded).
     * $altText = fallback text when Flex is not supported.
     */
    public function sendPushFlex(string $lineUserId, array $contents, string $altText): bool
    {
        $response = Http::withToken($this->channelAccessToken)
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'flex',
                        'altText' => $altText,
                        'contents' => $contents,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->body();
            self::$lastPushError = "HTTP {$response->status()}: {$body}";
            Log::warning('LINE Messaging API Flex push failed', [
                'status' => $response->status(),
                'body' => $body,
            ]);

            return false;
        }

        self::$lastPushError = null;

        return true;
    }

    /**
     * Build Flex bubble for class reminder (~1 hour before class).
     */
    public static function flexClassReminder(string $titleEn, string $titleZh, string $timeStr): array
    {
        return [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Class reminder / 課程提醒',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#FFFFFF',
                        'align' => 'center',
                    ],
                ],
                'backgroundColor' => '#1E40AF',
                'paddingAll' => '12px',
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $titleEn,
                        'weight' => 'bold',
                        'size' => 'xl',
                        'wrap' => true,
                    ],
                    [
                        'type' => 'text',
                        'text' => $titleZh,
                        'size' => 'sm',
                        'color' => '#666666',
                        'wrap' => true,
                    ],
                    ['type' => 'separator', 'margin' => 'md'],
                    [
                        'type' => 'box',
                        'layout' => 'baseline',
                        'contents' => [
                            ['type' => 'text', 'text' => '🕐', 'size' => 'sm', 'flex' => 0],
                            ['type' => 'text', 'text' => $timeStr, 'weight' => 'bold', 'size' => 'lg', 'flex' => 1],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Starts in ~1 hour / 約 1 小時後開始',
                        'size' => 'xs',
                        'color' => '#888888',
                        'margin' => 'md',
                        'wrap' => true,
                    ],
                ],
                'paddingAll' => '16px',
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'See you on the mat! / 墊上見！',
                        'size' => 'sm',
                        'color' => '#1E40AF',
                        'align' => 'center',
                        'weight' => 'bold',
                    ],
                ],
                'paddingAll' => '12px',
            ],
        ];
    }

    /**
     * Build Flex bubble for membership expiring in 3 days.
     */
    public static function flexMembershipExpiring(string $dateStr): array
    {
        return [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Expiry / 到期',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#FFFFFF',
                        'align' => 'center',
                    ],
                ],
                'backgroundColor' => '#B45309',
                'paddingAll' => '12px',
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Expires in 3 days / 3 天後到期',
                        'weight' => 'bold',
                        'size' => 'md',
                        'wrap' => true,
                    ],
                    [
                        'type' => 'text',
                        'text' => $dateStr,
                        'size' => 'xl',
                        'weight' => 'bold',
                        'color' => '#B45309',
                        'margin' => 'md',
                    ],
                    ['type' => 'separator', 'margin' => 'md'],
                    [
                        'type' => 'text',
                        'text' => 'Contact us to renew. / 如需續期請聯絡我們。',
                        'size' => 'sm',
                        'color' => '#666666',
                        'wrap' => true,
                    ],
                ],
                'paddingAll' => '16px',
            ],
        ];
    }

    /**
     * Build Flex bubble for class pass at zero (no classes left).
     */
    public static function flexClassPassZero(): array
    {
        return [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Class pass / 堂數',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#FFFFFF',
                        'align' => 'center',
                    ],
                ],
                'backgroundColor' => '#059669',
                'paddingAll' => '12px',
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'No classes left / 堂數已用完',
                        'weight' => 'bold',
                        'size' => 'md',
                        'wrap' => true,
                    ],
                    ['type' => 'separator', 'margin' => 'md'],
                    [
                        'type' => 'text',
                        'text' => 'Contact us to top up. / 如需再購買請聯絡我們。',
                        'size' => 'sm',
                        'color' => '#666666',
                        'wrap' => true,
                    ],
                ],
                'paddingAll' => '16px',
            ],
        ];
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

    public static function getLastPushError(): ?string
    {
        return self::$lastPushError;
    }
}
