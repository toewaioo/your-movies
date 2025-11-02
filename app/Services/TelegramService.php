<?php

namespace App\Services;

use App\Models\TelegramVideo;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
class TelegramService
{
    protected $telegram;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    /**
     * Fetch videos from private channel and store in database
     */
    public function fetchVideosFromChannel()
    {
        try {
            $updates = $this->telegram->getUpdates([
                'offset' => -100,
                'limit' => 100,
                'timeout' => 30,
            ]);

            foreach ($updates as $update) {
                if ($update->getMessage() && $update->getMessage()->has('video')) {
                    $this->storeVideo($update->getMessage());
                }
            }

            return true;
        } catch (TelegramSDKException $e) {
            Log::error('Error fetching videos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store video in database
     */
    protected function storeVideo($message)
    {
        $video = $message->getVideo();

        // Check if video already exists
        $existingVideo = TelegramVideo::where('file_unique_id', $video->fileUniqueId)->first();

        if (!$existingVideo) {
            TelegramVideo::create([
                'message_id' => $message->messageId,
                'file_id' => $video->fileId,
                'file_unique_id' => $video->fileUniqueId,
                'file_size' => $video->fileSize,
                'duration' => $video->duration,
                'width' => $video->width,
                'height' => $video->height,
                'file_name' => $video->fileName ?? null,
                'mime_type' => $video->mimeType ?? null,
                'caption' => $message->caption ?? null,
            ]);
        }
    }

    /**
     * Send video to user
     */
    public function sendVideoToUser($chatId, $videoId)
    {
        try {
            $video = TelegramVideo::find($videoId);

            if (!$video) {
                return false;
            }

            $response = $this->telegram->sendVideo([
                'chat_id' => $chatId,
                'video' => $video->file_id,
                'caption' => $video->caption,
            ]);

            return true;
        } catch (TelegramSDKException $e) {
            Log::error('Error sending video: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all videos for listing
     */
    public function getAllVideos()
    {
        return TelegramVideo::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get video by encoded ID
     */
    public function getVideoByEncodedId($encodedId)
    {
        $videoId = base64_decode($encodedId);
        return TelegramVideo::find($videoId);
    }
}
