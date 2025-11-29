<?php
// app/Services/StreamProxyService.php
namespace App\Services;

use App\Models\WatchLink;
use App\Models\DownloadLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
class StreamProxyService
{
    private const PROXY_CACHE_TTL = 3600; // 1 hour

    public function proxyStream(int $linkId, string $range = null): ?StreamedResponse
    {
        $link = WatchLink::find($linkId);

        if (!$link || !$link->is_active) {
            return null;
        }

        $headers = array_merge([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept' => 'video/webm,video/ogg,video/*;q=0.9,application/ogg;q=0.7,audio/*;q=0.6,*/*;q=0.5',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Range' => $range ?? 'bytes=0-',
            'Referer' => $link->url,
        ], $link->headers ?? []);

        return new StreamedResponse(function () use ($link, $headers) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(0) // No timeout for streaming
                    ->send('GET', $link->url, [
                        'stream' => true,
                    ]);

                // Forward headers
                $forwardHeaders = [
                    'Content-Type',
                    'Content-Length',
                    'Accept-Ranges',
                    'Content-Range',
                    'Cache-Control',
                    'Expires'
                ];

                foreach ($forwardHeaders as $header) {
                    if ($response->header($header)) {
                        header("{$header}: {$response->header($header)}");
                    }
                }

                // Stream the content
                echo $response->getBody()->getContents();
            } catch (\Exception $e) {
                abort(500, 'Stream proxy error: ' . $e->getMessage());
            }
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Range',
        ]);
    }

    public function getDirectDownloadUrl(int $downloadLinkId): ?string
    {
        $link = DownloadLink::find($downloadLinkId);

        if (!$link || !$link->is_active) {
            return null;
        }

        if ($link->requires_proxy) {
            return route('api.download.proxy', ['linkId' => $link->id]);
        }

        return $link->url;
    }

    public function proxyDownload(int $linkId): ?StreamedResponse
    {
        $link = DownloadLink::find($linkId);

        if (!$link || !$link->is_active) {
            return null;
        }

        $cacheKey = "download_proxy_{$linkId}";

        return Cache::remember($cacheKey, self::PROXY_CACHE_TTL, function () use ($link) {
            try {
                $response = Http::withHeaders($link->headers ?? [])
                    ->timeout(30)
                    ->get($link->url);

                if (!$response->successful()) {
                    return null;
                }

                $content = $response->body();
                $contentType = $response->header('Content-Type', 'application/octet-stream');

                return new StreamedResponse(function () use ($content) {
                    echo $content;
                }, 200, [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="' . $this->generateDownloadFilename($link) . '"',
                    'Content-Length' => strlen($content),
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    private function generateDownloadFilename(DownloadLink $link): string
    {
        $content = $link->movie ?? $link->episode;
        $quality = $link->quality;
        $extension = $link->file_format ?? 'mp4';

        if ($content instanceof \App\Models\Movie) {
            $title = Str::slug($content->title);
            return "{$title}-{$quality}.{$extension}";
        } else {
            $seriesTitle = Str::slug($content->season->series->title);
            $season = $content->season->season_number;
            $episode = $content->episode_number;
            return "{$seriesTitle}-S{$season}E{$episode}-{$quality}.{$extension}";
        }
    }

    public function validateStreamUrl(string $url): bool
    {
        try {
            $response = Http::timeout(5)
                ->head($url);

            $contentType = $response->header('Content-Type', '');

            return $response->successful() &&
                (str_contains($contentType, 'video/') ||
                    str_contains($contentType, 'application/'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
