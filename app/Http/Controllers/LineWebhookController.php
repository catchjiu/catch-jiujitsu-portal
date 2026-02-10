<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    /**
     * LINE Messaging API webhook. Receives follow/message events.
     * URL must be set in LINE Developers console and excluded from CSRF.
     */
    public function __invoke(Request $request, LineMessagingService $line): Response
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-Line-Signature', '');
        if (! $line->validateSignature($rawBody, $signature)) {
            Log::warning('LINE webhook invalid signature');

            return response('', 403);
        }

        $body = json_decode($rawBody, true) ?? [];
        $events = $body['events'] ?? [];

        foreach ($events as $event) {
            $this->handleEvent($event, $line);
        }

        return response('', 200);
    }

    private function handleEvent(array $event, LineMessagingService $line): void
    {
        $locale = config('app.locale');
        $type = $event['type'] ?? '';
        $replyToken = $event['replyToken'] ?? null;
        $source = $event['source'] ?? [];
        $userId = $source['userId'] ?? null;

        if ($type === 'follow') {
            $msg = $locale === 'zh-TW'
                ? '請在網站設定頁取得 6 位數連結碼，然後在這裡回覆該數字以完成連結。'
                : 'Get your 6-digit link code from the portal Settings, then reply here with that code to connect.';
            if ($replyToken) {
                $line->reply($replyToken, $msg);
            }
            return;
        }

        if ($type === 'message') {
            $message = $event['message'] ?? [];
            if (($message['type'] ?? '') !== 'text') {
                return;
            }
            $text = trim($message['text'] ?? '');
            if (! preg_match('/^\d{6}$/', $text)) {
                if ($replyToken) {
                    $line->reply($replyToken, $locale === 'zh-TW'
                        ? '請回覆 6 位數連結碼（在網站設定頁取得）。'
                        : 'Please reply with your 6-digit link code from the portal Settings.');
                }
                return;
            }

            $cacheKey = 'line_link:' . $text;
            $portalUserId = Cache::get($cacheKey);
            if (! $portalUserId) {
                if ($replyToken) {
                    $line->reply($replyToken, $locale === 'zh-TW'
                        ? '連結碼無效或已過期，請在網站重新取得。'
                        : 'Invalid or expired code. Get a new code from the portal.');
                }
                return;
            }

            $user = User::find($portalUserId);
            if (! $user) {
                Cache::forget($cacheKey);
                if ($replyToken) {
                    $line->reply($replyToken, $locale === 'zh-TW'
                        ? '連結失敗，請重試。'
                        : 'Link failed. Please try again.');
                }
                return;
            }

            $user->update(['line_id' => $userId]);
            Cache::forget($cacheKey);

            if ($replyToken) {
                $line->reply($replyToken, $locale === 'zh-TW'
                    ? '已連結！您將在此收到課程提醒。'
                    : "Connected! You'll receive class reminders here.");
            }
        }
    }
}
