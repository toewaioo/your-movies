<?php
// app/Services/WatchHistoryService.php
namespace App\Services;

use App\Models\WatchHistory;
use App\Models\User;
use App\Models\Movie;
use App\Models\Episode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WatchHistoryService
{
    public function getUserWatchHistory(User $user)
    {
        return WatchHistory::with([
            'movie',
            'episode.season.series'
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);
    }

    public function getContinueWatching(User $user): Collection
    {
        return WatchHistory::with([
            'movie',
            'episode.season.series'
        ])
            ->where('user_id', $user->id)
            ->where('completed', false)
            ->where('percent_watched', '>', 5) // At least 5% watched
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function updateProgress(User $user, array $data): WatchHistory
    {
        return DB::transaction(function () use ($user, $data) {
            $watchHistory = WatchHistory::firstOrCreate([
                'user_id' => $user->id,
                'movie_id' => $data['movie_id'] ?? null,
                'episode_id' => $data['episode_id'] ?? null,
            ]);

            $watchHistory->updateProgress(
                $data['last_position_seconds'],
                $data['duration_seconds']
            );

            return $watchHistory->fresh(['movie', 'episode.season.series']);
        });
    }

    public function clearUserHistory(User $user): void
    {
        WatchHistory::where('user_id', $user->id)->delete();
    }

    public function removeFromHistory(WatchHistory $watchHistory): void
    {
        $watchHistory->delete();
    }

    public function markAsCompleted(WatchHistory $watchHistory): void
    {
        $watchHistory->update([
            'completed' => true,
            'percent_watched' => 100,
        ]);
    }

    public function getWatchStats(User $user): array
    {
        $totalWatched = WatchHistory::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        $totalTimeWatched = WatchHistory::where('user_id', $user->id)
            ->where('completed', true)
            ->sum('duration_seconds');

        $recentlyWatched = WatchHistory::with(['movie', 'episode.season.series'])
            ->where('user_id', $user->id)
            ->where('completed', true)
            ->latest()
            ->limit(10)
            ->get();

        return [
            'total_watched' => $totalWatched,
            'total_time_watched_seconds' => $totalTimeWatched,
            'total_time_watched_formatted' => $this->formatTime($totalTimeWatched),
            'recently_watched' => $recentlyWatched,
        ];
    }

    private function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    public function syncWatchProgress(User $user, array $progressData): void
    {
        DB::transaction(function () use ($user, $progressData) {
            foreach ($progressData as $progress) {
                $this->updateProgress($user, $progress);
            }
        });
    }

    public function getRecommendedContent(User $user, int $limit = 10): Collection
    {
        // Get user's watch history genres
        $watchedGenres = WatchHistory::where('user_id', $user->id)
            ->whereHas('movie.genres')
            ->with('movie.genres')
            ->get()
            ->flatMap(function ($history) {
                return $history->movie ? $history->movie->genres : collect();
            })
            ->pluck('id')
            ->unique();

        // Get movies from same genres that user hasn't watched
        return Movie::whereHas('genres', function ($query) use ($watchedGenres) {
            $query->whereIn('genres.id', $watchedGenres);
        })
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('movie_id')
                    ->from('watch_history')
                    ->where('user_id', $user->id)
                    ->whereNotNull('movie_id');
            })
            ->public()
            ->released()
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get();
    }
}
