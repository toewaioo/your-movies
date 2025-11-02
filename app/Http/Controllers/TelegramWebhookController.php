<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use Telegram\Bot\Api;
class TelegramWebhookController extends Controller
{
    //
    protected $telegram;
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->telegramService = $telegramService;
    }

    public function handleWebhook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();

        if ($update->isType('message')) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Handle /start command with parameter
            if (strpos($text, '/start') === 0) {
                $parts = explode(' ', $text);
                if (count($parts) > 1) {
                    $this->handleStartWithParameter($chatId, $parts[1]);
                } else {
                    $this->sendWelcomeMessage($chatId);
                }
            }

            // Handle /videos command
            if ($text === '/videos') {
                $this->sendVideoList($chatId);
            }
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleStartWithParameter($chatId, $parameter)
    {
        try {
            $videoId = base64_decode($parameter);

            if ($videoId && is_numeric($videoId)) {
                $this->telegramService->sendVideoToUser($chatId, $videoId);
            } else {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Invalid video link. Please try again.',
                ]);
            }
        } catch (\Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Error sending video. Please try again.',
            ]);
        }
    }

    protected function sendWelcomeMessage($chatId)
    {
        $message = "Welcome to Video Delivery Bot! 🎬\n\n";
        $message .= "Use /videos to see all available videos\n";
        $message .= "Or use a specific video link to get a video directly.";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
        ]);
    }

    protected function sendVideoList($chatId)
    {
        $videos = $this->telegramService->getAllVideos();

        if ($videos->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'No videos available at the moment.',
            ]);
            return;
        }

        $message = "Available Videos: \n\n";

        foreach ($videos as $video) {
            $encodedId = base64_encode($video->id);
            $botUsername = env('TELEGRAM_BOT_USERNAME');
            $videoUrl = "https://t.me/{$botUsername}?start={$encodedId}";

            $caption = $video->caption ?: "Video #{$video->id}";
            $message .= "🎥 {$caption}\n";
            $message .= "🔗 {$videoUrl}\n\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'disable_web_page_preview' => true,
        ]);
    }
}
