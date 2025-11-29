<?php
// app/Services/SearchService.php
namespace App\Services;

use App\Models\Movie;
use App\Models\Series;
use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SearchService
{
    public function globalSearch(string $query, array $filters = []): array
    {
        $results = [];

        // Search movies
        if (!isset($filters['type']) || $filters['type'] === 'movie') {
            $results['movies'] = $this->searchMovies($query, $filters);
        }

        // Search series
        if (!isset($filters['type']) || $filters['type'] === 'series') {
            $results['series'] = $this->searchSeries($query, $filters);
        }

        // Search persons
        if (!isset($filters['type']) || $filters['type'] === 'person') {
            $results['persons'] = $this->searchPersons($query, $filters);
        }

        $results['total'] =
            ($results['movies']->total() ?? 0) +
            ($results['series']->total() ?? 0) +
            ($results['persons']->total() ?? 0);

        return $results;
    }

    public function searchMovies(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = Movie::with(['genres'])
            ->public()
            ->released()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('original_title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            });

        // Apply filters
        if (!empty($filters['genre'])) {
            $search->whereHas('genres', function ($q) use ($filters) {
                $q->where('slug', $filters['genre']);
            });
        }

        if (!empty($filters['year'])) {
            $search->whereYear('release_date', $filters['year']);
        }

        if (!empty($filters['rating'])) {
            $search->where('rating_average', '>=', $filters['rating']);
        }

        if (!empty($filters['vip_only'])) {
            $search->vipOnly();
        }

        return $search->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function searchSeries(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = Series::with(['genres'])
            ->public()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('original_title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            });

        // Apply filters
        if (!empty($filters['genre'])) {
            $search->whereHas('genres', function ($q) use ($filters) {
                $q->where('slug', $filters['genre']);
            });
        }

        if (!empty($filters['year'])) {
            $search->where('release_year_start', '<=', $filters['year'])
                ->where(function ($q) use ($filters) {
                    $q->whereNull('release_year_end')
                        ->orWhere('release_year_end', '>=', $filters['year']);
                });
        }

        if (!empty($filters['status'])) {
            $search->where('status', $filters['status']);
        }

        return $search->orderBy('release_year_start', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function searchPersons(string $query, array $filters = []): LengthAwarePaginator
    {
        $search = Person::where(function ($q) use ($query) {
            $q->where('name', 'ilike', "%{$query}%")
                ->orWhere('biography', 'ilike', "%{$query}%");
        });

        if (!empty($filters['role'])) {
            $search->whereHas('roles', function ($q) use ($filters) {
                $q->where('role_type', $filters['role']);
            });
        }

        return $search->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function advancedSearch(array $criteria): array
    {
        $results = [];

        // Movie advanced search
        if (isset($criteria['movies'])) {
            $results['movies'] = $this->advancedMovieSearch($criteria['movies']);
        }

        // Series advanced search
        if (isset($criteria['series'])) {
            $results['series'] = $this->advancedSeriesSearch($criteria['series']);
        }

        // Person advanced search
        // if (isset($criteria['persons'])) {
        //     $results['persons'] = $this->advancedPersonSearch($criteria['persons']);
        // }

        return $results;
    }

    private function advancedMovieSearch(array $criteria): LengthAwarePaginator
    {
        $search = Movie::with(['genres', 'actors.person']);

        if (!empty($criteria['query'])) {
            $search->where(function ($q) use ($criteria) {
                $q->where('title', 'ilike', "%{$criteria['query']}%")
                    ->orWhere('description', 'ilike', "%{$criteria['query']}%");
            });
        }

        if (!empty($criteria['genres'])) {
            $search->whereHas('genres', function ($q) use ($criteria) {
                $q->whereIn('slug', $criteria['genres']);
            });
        }

        if (!empty($criteria['year_from'])) {
            $search->whereYear('release_date', '>=', $criteria['year_from']);
        }

        if (!empty($criteria['year_to'])) {
            $search->whereYear('release_date', '<=', $criteria['year_to']);
        }

        if (!empty($criteria['rating_from'])) {
            $search->where('rating_average', '>=', $criteria['rating_from']);
        }

        if (!empty($criteria['actors'])) {
            $search->whereHas('actors.person', function ($q) use ($criteria) {
                $q->whereIn('name', $criteria['actors']);
            });
        }

        return $search->public()
            ->released()
            ->orderBy('release_date', 'desc')
            ->paginate($criteria['per_page'] ?? 20);
    }

    private function advancedSeriesSearch(array $criteria): LengthAwarePaginator
    {
        $search = Series::with(['genres']);

        if (!empty($criteria['query'])) {
            $search->where(function ($q) use ($criteria) {
                $q->where('title', 'ilike', "%{$criteria['query']}%")
                    ->orWhere('description', 'ilike', "%{$criteria['query']}%");
            });
        }

        if (!empty($criteria['status'])) {
            $search->where('status', $criteria['status']);
        }

        return $search->public()
            ->orderBy('release_year_start', 'desc')
            ->paginate($criteria['per_page'] ?? 20);
    }

    public function getSearchSuggestions(string $query): array
    {
        $suggestions = [];

        // Movie suggestions
        $movieSuggestions = Movie::public()
            ->released()
            ->where('title', 'ilike', "{$query}%")
            ->limit(5)
            ->get(['id', 'title', 'release_date', 'poster_url'])
            ->map(function ($movie) {
                return [
                    'type' => 'movie',
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'year' => $movie->release_date->year,
                    'poster' => $movie->poster_url,
                ];
            });

        // Series suggestions
        $seriesSuggestions = Series::public()
            ->where('title', 'ilike', "{$query}%")
            ->limit(5)
            ->get(['id', 'title', 'release_year_start', 'poster_url'])
            ->map(function ($series) {
                return [
                    'type' => 'series',
                    'id' => $series->id,
                    'title' => $series->title,
                    'year' => $series->release_year_start,
                    'poster' => $series->poster_url,
                ];
            });

        // Person suggestions
        $personSuggestions = Person::where('name', 'ilike', "{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'avatar_url'])
            ->map(function ($person) {
                return [
                    'type' => 'person',
                    'id' => $person->id,
                    'name' => $person->name,
                    'avatar' => $person->avatar_url,
                ];
            });

        return [
            'movies' => $movieSuggestions,
            'series' => $seriesSuggestions,
            'persons' => $personSuggestions,
        ];
    }
}
