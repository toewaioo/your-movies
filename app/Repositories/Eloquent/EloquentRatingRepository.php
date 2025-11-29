<?php
// app/Repositories/Eloquent/EloquentRatingRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Rating;
use App\Models\User;
use App\Repositories\Interfaces\RatingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentRatingRepository implements RatingRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Rating::with(['user', 'movie', 'episode.season.series']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): ?Rating
    {
        return Rating::with(['user', 'movie', 'episode.season.series'])->find($id);
    }

    public function create(array $data): Rating
    {
        return Rating::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return false;
        }

        return $rating->update($data);
    }

    public function delete(int $id): bool
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return false;
        }

        return $rating->delete();
    }

    public function getUserRatings(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Rating::with(['movie', 'episode.season.series'])
            ->where('user_id', $user->id);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getUserRatingForContent(User $user, string $type, int $contentId): ?Rating
    {
        $column = $type === 'movie' ? 'movie_id' : 'episode_id';

        return Rating::where('user_id', $user->id)
            ->where($column, $contentId)
            ->first();
    }

    public function getContentRatings($content, string $type, int $perPage = 20): LengthAwarePaginator
    {
        $column = $type === 'movie' ? 'movie_id' : 'episode_id';

        return Rating::with('user')
            ->where($column, $content->id)
            ->whereNotNull('review_text')
            ->latest()
            ->paginate($perPage);
    }

    public function getAverageRating($content, string $type): array
    {
        $column = $type === 'movie' ? 'movie_id' : 'episode_id';

        $stats = Rating::where($column, $content->id)
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        return [
            'average' => round($stats->average ?? 0, 1),
            'count' => $stats->count ?? 0,
        ];
    }

    public function getRatingDistribution($content, string $type): array
    {
        $column = $type === 'movie' ? 'movie_id' : 'episode_id';

        return Rating::where($column, $content->id)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'movie') {
                $query->whereNotNull('movie_id');
            } elseif ($filters['type'] === 'episode') {
                $query->whereNotNull('episode_id');
            }
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (!empty($filters['has_review'])) {
            $query->whereNotNull('review_text');
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
    }
}
