<?php
// app/Repositories/Eloquent/EloquentWatchHistoryRepository.php
namespace App\Repositories\Eloquent;

use App\Models\WatchHistory;
use App\Models\User;
use App\Repositories\Interfaces\WatchHistoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentWatchHistoryRepository implements WatchHistoryRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = WatchHistory::with(['movie', 'episode.season.series']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('updated_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): ?WatchHistory
    {
        return WatchHistory::with(['movie', 'episode.season.series'])->find($id);
    }

    public function create(array $data): WatchHistory
    {
        return WatchHistory::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $watchHistory = WatchHistory::find($id);

        if (!$watchHistory) {
            return false;
        }

        return $watchHistory->update($data);
    }

    public function delete(int $id): bool
    {
        $watchHistory = WatchHistory::find($id);

        if (!$watchHistory) {
            return false;
        }

        return $watchHistory->delete();
    }

    public function getUserWatchHistory(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = WatchHistory::with(['movie', 'episode.season.series'])
            ->where('user_id', $user->id);

        $this->applyFilters($query, $filters);

        return $query->orderBy('updated_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getContinueWatching(User $user): Collection
    {
        return WatchHistory::with(['movie', 'episode.season.series'])
            ->where('user_id', $user->id)
            ->where('completed', false)
            ->where('percent_watched', '>', 5)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function updateProgress(int $id, array $data): bool
    {
        $watchHistory = WatchHistory::find($id);

        if (!$watchHistory) {
            return false;
        }

        $position = $data['last_position_seconds'];
        $duration = $data['duration_seconds'];
        $percent = $duration > 0 ? ($position / $duration) * 100 : 0;
        $completed = $percent >= 90;

        return $watchHistory->update([
            'last_position_seconds' => $position,
            'duration_seconds' => $duration,
            'percent_watched' => $percent,
            'completed' => $completed,
        ]);
    }

    public function clearUserHistory(int $userId): bool
    {
        return WatchHistory::where('user_id', $userId)->delete() > 0;
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

        $mostWatchedGenres = DB::table('watch_history')
            ->join('movies', 'watch_history.movie_id', '=', 'movies.id')
            ->join('genre_movie', 'movies.id', '=', 'genre_movie.movie_id')
            ->join('genres', 'genre_movie.genre_id', '=', 'genres.id')
            ->where('watch_history.user_id', $user->id)
            ->where('watch_history.completed', true)
            ->select('genres.name', DB::raw('COUNT(*) as count'))
            ->groupBy('genres.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_watched' => $totalWatched,
            'total_time_watched_seconds' => $totalTimeWatched,
            'total_time_watched_formatted' => $this->formatTime($totalTimeWatched),
            'recently_watched' => $recentlyWatched,
            'most_watched_genres' => $mostWatchedGenres,
        ];
    }

    public function getRecommendedContent(User $user, int $limit = 10): Collection
    {
        // Get user's watched genres
        $watchedGenres = DB::table('watch_history')
            ->join('movies', 'watch_history.movie_id', '=', 'movies.id')
            ->join('genre_movie', 'movies.id', '=', 'genre_movie.movie_id')
            ->where('watch_history.user_id', $user->id)
            ->where('watch_history.completed', true)
            ->pluck('genre_movie.genre_id')
            ->unique();

        // Get movies from same genres that user hasn't watched
        return \App\Models\Movie::whereHas('genres', function ($query) use ($watchedGenres) {
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

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'movie') {
                $query->whereNotNull('movie_id');
            } elseif ($filters['type'] === 'episode') {
                $query->whereNotNull('episode_id');
            }
        }

        if (!empty($filters['completed'])) {
            $query->where('completed', $filters['completed']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
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
}
