<?php
// app/Repositories/Eloquent/EloquentSeriesRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;
use App\Repositories\Interfaces\SeriesRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentSeriesRepository implements SeriesRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Series::with(['genres', 'seasons.episodes','seasons.episodes.downloadLinks','seasons.episodes.watchLinks',])
            ->public();

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_year_start', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): ?Series
    {
        return Series::with([
            'genres',
            'seasons.episodes',
            'persons.person',
            'actors.person'
        ])->find($id);
    }

    public function findBySlug(string $slug): ?Series
    {
        return Series::with([
            'genres',
            'seasons.episodes',
            'persons.person',
            'actors.person'
        ])->where('slug', $slug)->first();
    }

    public function create(array $data): Series
    {
        return Series::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $series = Series::find($id);

        if (!$series) {
            return false;
        }

        return $series->update($data);
    }

    public function delete(int $id): bool
    {
        $series = Series::find($id);

        if (!$series) {
            return false;
        }

        return $series->delete();
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = Series::with(['genres', 'seasons'])
            ->public()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('original_title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('persons.person', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    });
            });

        $this->applyFilters($searchQuery, $filters);

        return $searchQuery->orderBy('release_year_start', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getTrending(int $limit = 10): Collection
    {
        return Series::with(['genres'])
            ->public()
            ->orderBy('rating_count', 'desc')
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSeason(string $seriesSlug, int $seasonNumber): ?Season
    {
        return Season::with(['episodes', 'series'])
            ->whereHas('series', function ($query) use ($seriesSlug) {
                $query->where('slug', $seriesSlug);
            })
            ->where('season_number', $seasonNumber)
            ->first();
    }

    public function getEpisode(string $seriesSlug, int $seasonNumber, int $episodeNumber): ?Episode
    {
        return Episode::with([
            'season.series',
            'persons.person',
            'watchLinks',
            'downloadLinks'
        ])
            ->whereHas('season.series', function ($query) use ($seriesSlug) {
                $query->where('slug', $seriesSlug);
            })
            ->whereHas('season', function ($query) use ($seasonNumber) {
                $query->where('season_number', $seasonNumber);
            })
            ->where('episode_number', $episodeNumber)
            ->first();
    }

    public function getByGenre(string $genreSlug, array $filters = []): LengthAwarePaginator
    {
        $query = Series::with(['genres', 'seasons'])
            ->public()
            ->whereHas('genres', function ($q) use ($genreSlug) {
                $q->where('slug', $genreSlug);
            });

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_year_start', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getByStatus(string $status, array $filters = []): LengthAwarePaginator
    {
        $query = Series::with(['genres', 'seasons'])
            ->public()
            ->where('status', $status);

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_year_start', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getLatestEpisodes(int $limit = 10): Collection
    {
        return Episode::with(['season.series'])
            ->whereHas('season.series', function ($query) {
                $query->public();
            })
            ->orderBy('air_date', 'desc')
            ->limit($limit)
            ->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['genre'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->where('slug', $filters['genre']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->where('release_year_start', '<=', $filters['year'])
                ->where(function ($q) use ($filters) {
                    $q->whereNull('release_year_end')
                        ->orWhere('release_year_end', '>=', $filters['year']);
                });
        }

        if (!empty($filters['vip_only'])) {
            $query->vipOnly();
        }

        if (!empty($filters['country'])) {
            $query->where('country', 'like', "%{$filters['country']}%");
        }

        if (!empty($filters['language'])) {
            $query->where('language', $filters['language']);
        }
    }
}
