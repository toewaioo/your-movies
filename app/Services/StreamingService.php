<?php
// app/Services/StreamingService.php
namespace App\Services;

use App\Models\Movie;
use App\Models\Episode;
use App\Models\User;
use App\Models\WatchLink;
use App\Repositories\Interfaces\StreamingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StreamingService
{
    private const LINK_TIMEOUT = 10;
    private const HEALTH_CHECK_CACHE = 300; // 5 minutes

    public function __construct(
        private StreamingRepositoryInterface $streamingRepository
    ) {}

    public function getStreamingLinks(Movie|Episode $content, User $user, string $quality = null): array
    {
        $bestLink = $this->streamingRepository->findBestWatchLink($content, $user, $quality);

        if ($content instanceof Movie) {
            $allLinks = $this->streamingRepository->getWatchLinksForMovie($content, $user, $quality);
        } else {
            $allLinks = $this->streamingRepository->getWatchLinksForEpisode($content, $user, $quality);
        }

        return [
            'best_link' => $bestLink,
            'alternative_links' => $allLinks->where('server_name', '!=', $bestLink['server_name'])->values(),
            'available_qualities' => $allLinks->pluck('quality')->unique()->values(),
            'total_links' => $allLinks->count(),
        ];
    }

    public function getProxiedStreamUrl(int $linkId, User $user): ?string
    {
        $link = WatchLink::find($linkId);

        if (!$link || !$this->canUserAccessLink($link, $user)) {
            return null;
        }

        // For external URLs that need proxying
        if ($link->requires_proxy) {
            return route('api.stream.proxy', ['linkId' => $linkId]);
        }

        // For direct URLs
        return $link->url;
    }

    public function checkLinkHealth(WatchLink $link): bool
    {
        $cacheKey = "link_health_{$link->id}";

        return Cache::remember($cacheKey, self::HEALTH_CHECK_CACHE, function () use ($link) {
            try {
                $startTime = microtime(true);

                $response = Http::timeout(self::LINK_TIMEOUT)
                    ->withHeaders($link->headers ?? [])
                    ->get($link->url);

                $responseTime = (microtime(true) - $startTime) * 1000;
                $isWorking = $response->successful();

                $this->streamingRepository->updateLinkHealth(
                    $link->id,
                    $isWorking,
                    (int) $responseTime
                );

                Log::info("Link health check", [
                    'link_id' => $link->id,
                    'url' => $link->url,
                    'working' => $isWorking,
                    'response_time' => $responseTime,
                    'status' => $response->status()
                ]);

                return $isWorking;
            } catch (\Exception $e) {
                Log::error("Link health check failed", [
                    'link_id' => $link->id,
                    'url' => $link->url,
                    'error' => $e->getMessage()
                ]);

                $this->streamingRepository->updateLinkHealth(
                    $link->id,
                    false,
                    null,
                    $e->getMessage()
                );
                return false;
            }
        });
    }

    public function bulkHealthCheck(): array
    {
        $links = WatchLink::active()->get();
        $results = [
            'checked' => 0,
            'working' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($links as $link) {
            $isWorking = $this->checkLinkHealth($link);

            $results['checked']++;
            if ($isWorking) {
                $results['working']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'link_id' => $link->id,
                'server' => $link->server_name,
                'quality' => $link->quality,
                'working' => $isWorking,
                'success_rate' => $link->success_rate
            ];
        }

        return $results;
    }

    public function getDownloadLinks(Movie|Episode $content, User $user, string $quality = null): Collection
    {
        if ($content instanceof Movie) {
            return $this->streamingRepository->getDownloadLinksForMovie($content, $user, $quality);
        } else {
            return $this->streamingRepository->getDownloadLinksForEpisode($content, $user, $quality);
        }
    }

    public function getAlternativeLinks(Movie|Episode $content, string $currentServer, User $user): Collection
    {
        return $this->streamingRepository->getAlternativeLinks($content, $currentServer, $user);
    }

    public function addWatchLink(array $data): WatchLink
    {
        return WatchLink::create($data);
    }

    public function updateWatchLink(int $linkId, array $data): WatchLink
    {
        $link = WatchLink::findOrFail($linkId);
        $link->update($data);

        // Perform health check after update
        $this->checkLinkHealth($link);

        return $link->fresh();
    }

    public function deleteWatchLink(int $linkId): bool
    {
        $link = WatchLink::findOrFail($linkId);
        return $link->delete();
    }

    private function canUserAccessLink(WatchLink $link, User $user): bool
    {
        if ($link->is_vip_only && !$user->isVIP()) {
            return false;
        }

        if (!$link->is_active) {
            return false;
        }

        return true;
    }

    public function getUserAccessibleLinks(Movie|Episode $content, User $user): Collection
    {
        if ($content instanceof Movie) {
            $links = WatchLink::where('movie_id', $content->id);
        } else {
            $links = WatchLink::where('episode_id', $content->id);
        }

        return $links->active()
            ->forUser($user)
            ->byPriority()
            ->get();
    }

    public function getLinkStatistics(): array
    {
        $totalLinks = WatchLink::count();
        $activeLinks = WatchLink::active()->count();
        $averageSuccessRate = WatchLink::active()->avg('success_rate') ?? 0;

        $qualityStats = WatchLink::selectRaw('quality, COUNT(*) as count, AVG(success_rate) as avg_success')
            ->groupBy('quality')
            ->get();

        $serverStats = WatchLink::selectRaw('server_name, COUNT(*) as count, AVG(success_rate) as avg_success')
            ->groupBy('server_name')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'total_links' => $totalLinks,
            'active_links' => $activeLinks,
            'inactive_links' => $totalLinks - $activeLinks,
            'average_success_rate' => round($averageSuccessRate, 2),
            'quality_distribution' => $qualityStats,
            'server_distribution' => $serverStats,
        ];
    }
}
