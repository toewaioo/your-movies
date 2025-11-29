<?php
// app/Repositories/Eloquent/EloquentStreamingRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Movie;
use App\Models\Episode;
use App\Models\User;
use App\Models\WatchLink;
use App\Models\DownloadLink;
use App\Models\LinkHealthCheck;
use App\Repositories\Interfaces\StreamingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentStreamingRepository implements StreamingRepositoryInterface
{
    public function getWatchLinksForMovie(Movie $movie, User $user, string $quality = null): Collection
    {
        $query = WatchLink::with('healthChecks')
            ->where('movie_id', $movie->id)
            ->active()
            ->forUser($user)
            ->byPriority();

        if ($quality) {
            $query->byQuality($quality);
        }

        return $query->get()->map(function ($link) {
            return $this->formatWatchLink($link);
        });
    }

    public function getWatchLinksForEpisode(Episode $episode, User $user, string $quality = null): Collection
    {
        $query = WatchLink::with('healthChecks')
            ->where('episode_id', $episode->id)
            ->active()
            ->forUser($user)
            ->byPriority();

        if ($quality) {
            $query->byQuality($quality);
        }

        return $query->get()->map(function ($link) {
            return $this->formatWatchLink($link);
        });
    }

    public function getDownloadLinksForMovie(Movie $movie, User $user, string $quality = null): Collection
    {
        $query = DownloadLink::with('healthChecks')
            ->where('movie_id', $movie->id)
            ->active()
            ->forUser($user)
            ->byPriority();

        if ($quality) {
            $query->byQuality($quality);
        }

        return $query->get()->map(function ($link) {
            return [
                'id' => $link->id,
                'quality' => $link->quality,
                'server_name' => $link->server_name,
                'url' => $link->url,
                'file_size' => $link->file_size,
                'file_format' => $link->file_format,
                'requires_proxy' => $link->requires_proxy,
                'is_vip_only' => $link->is_vip_only,
                'success_rate' => $link->success_rate,
                'last_checked' => $link->last_checked_at,
            ];
        });
    }

    public function getDownloadLinksForEpisode(Episode $episode, User $user, string $quality = null): Collection
    {
        $query = DownloadLink::with('healthChecks')
            ->where('episode_id', $episode->id)
            ->active()
            ->forUser($user)
            ->byPriority();

        if ($quality) {
            $query->byQuality($quality);
        }

        return $query->get()->map(function ($link) {
            return [
                'id' => $link->id,
                'quality' => $link->quality,
                'server_name' => $link->server_name,
                'url' => $link->url,
                'file_size' => $link->file_size,
                'file_format' => $link->file_format,
                'requires_proxy' => $link->requires_proxy,
                'is_vip_only' => $link->is_vip_only,
                'success_rate' => $link->success_rate,
                'last_checked' => $link->last_checked_at,
            ];
        });
    }

    public function findBestWatchLink(Movie|Episode $content, User $user, string $preferredQuality = null): ?array
    {
        $contentType = $content instanceof Movie ? 'movie' : 'episode';
        $contentId = $content->id;

        $query = WatchLink::with('healthChecks')
            ->where("{$contentType}_id", $contentId)
            ->active()
            ->forUser($user)
            ->byPriority();

        // Try preferred quality first
        if ($preferredQuality) {
            $preferredLink = (clone $query)->byQuality($preferredQuality)->first();
            if ($preferredLink) {
                return $this->formatWatchLink($preferredLink);
            }
        }

        // Fallback to highest available quality
        $qualityOrder = ['4K', '1080p', '720p', '480p', '360p'];
        foreach ($qualityOrder as $quality) {
            $link = (clone $query)->byQuality($quality)->first();
            if ($link) {
                return $this->formatWatchLink($link);
            }
        }

        return null;
    }

    public function getAlternativeLinks(Movie|Episode $content, string $currentServer, User $user): Collection
    {
        $contentType = $content instanceof Movie ? 'movie' : 'episode';
        $contentId = $content->id;

        return WatchLink::with('healthChecks')
            ->where("{$contentType}_id", $contentId)
            ->where('server_name', '!=', $currentServer)
            ->active()
            ->forUser($user)
            ->byPriority()
            ->get()
            ->map(function ($link) {
                return $this->formatWatchLink($link);
            });
    }

    public function updateLinkHealth(int $linkId, bool $isWorking, int $responseTime = null, string $errorMessage = null): void
    {
        DB::transaction(function () use ($linkId, $isWorking, $responseTime, $errorMessage) {
            $link = WatchLink::find($linkId);

            if (!$link) return;

            // Create health check record
            LinkHealthCheck::create([
                'linkable_type' => WatchLink::class,
                'linkable_id' => $linkId,
                'is_working' => $isWorking,
                'response_time_ms' => $responseTime,
                'error_message' => $errorMessage,
                'checked_at' => now(),
            ]);

            // Update link status
            if ($isWorking) {
                $link->markAsWorking();
            } else {
                $link->markAsFailed();
            }
        });
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

        $recentHealthChecks = LinkHealthCheck::where('created_at', '>=', now()->subDay())
            ->selectRaw('is_working, COUNT(*) as count')
            ->groupBy('is_working')
            ->get();

        return [
            'total_links' => $totalLinks,
            'active_links' => $activeLinks,
            'inactive_links' => $totalLinks - $activeLinks,
            'average_success_rate' => round($averageSuccessRate, 2),
            'quality_distribution' => $qualityStats,
            'server_distribution' => $serverStats,
            'recent_health_checks' => $recentHealthChecks,
        ];
    }

    private function formatWatchLink(WatchLink $link): array
    {
        return [
            'id' => $link->id,
            'quality' => $link->quality,
            'server_name' => $link->server_name,
            'source_type' => $link->source_type,
            'url' => $link->url,
            'embed_code' => $link->embed_code,
            'requires_proxy' => $link->requires_proxy,
            'is_vip_only' => $link->is_vip_only,
            'priority' => $link->priority,
            'success_rate' => $link->success_rate,
            'last_checked' => $link->last_checked_at,
            'headers' => $link->headers,
        ];
    }
}
