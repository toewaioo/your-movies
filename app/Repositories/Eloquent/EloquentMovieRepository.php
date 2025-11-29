<?php
// app/Repositories/Eloquent/EloquentMovieRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Movie;
use App\Repositories\Interfaces\MovieRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentMovieRepository implements MovieRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Movie::with(['genres', 'actors.person', 'downloadLinks', 'watchLinks'])
            ->public()
            ->released();

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): ?Movie
    {
        return Movie::with([
            'genres',
            'persons.person',
            'watchLinks',
            'downloadLinks',
            'ratings.user'
        ])->find($id);
    }

    public function findBySlug(string $slug): ?Movie
    {
        return Movie::with([
            'genres',
            'persons.person',
            'watchLinks',
            'downloadLinks',
            'ratings.user'
        ])->where('slug', $slug)->first();
    }

    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return false;
        }

        return $movie->update($data);
    }

    public function delete(int $id): bool
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return false;
        }

        return $movie->delete();
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = Movie::with(['genres', 'actors.person'])
            ->public()
            ->released()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('original_title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('persons.person', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    });
            });

        $this->applyFilters($searchQuery, $filters);

        return $searchQuery->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getTrending(int $limit = 10): Collection
    {
        return Movie::with(['genres'])
            ->public()
            ->released()
            ->orderBy('view_count', 'desc')
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRelated(Movie $movie, int $limit = 6): Collection
    {
        $genreIds = $movie->genres->pluck('id');

        return Movie::with(['genres'])
            ->public()
            ->released()
            ->where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($genreIds) {
                $query->whereIn('genres.id', $genreIds);
            })
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByGenre(string $genreSlug, array $filters = []): LengthAwarePaginator
    {
        $query = Movie::with(['genres', 'actors.person'])
            ->public()
            ->released()
            ->whereHas('genres', function ($q) use ($genreSlug) {
                $q->where('slug', $genreSlug);
            });

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getUpcoming(array $filters = []): LengthAwarePaginator
    {
        $query = Movie::with(['genres'])
            ->public()
            ->upcoming()
            ->where('release_date', '>', now());

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_date', 'asc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getLatest(array $filters = []): LengthAwarePaginator
    {
        $query = Movie::with(['genres'])
            ->public()
            ->released();

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getByYear(int $year, array $filters = []): LengthAwarePaginator
    {
        $query = Movie::with(['genres'])
            ->public()
            ->released()
            ->whereYear('release_date', $year);

        $this->applyFilters($query, $filters);

        return $query->orderBy('release_date', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function incrementViewCount(int $id): bool
    {
        return Movie::where('id', $id)->increment('view_count');
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['genre'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->where('slug', $filters['genre']);
            });
        }

        if (!empty($filters['year'])) {
            $query->whereYear('release_date', $filters['year']);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating_average', '>=', $filters['rating']);
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

        if (!empty($filters['actor'])) {
            $query->whereHas('actors.person', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['actor']}%");
            });
        }

        if (!empty($filters['director'])) {
            $query->whereHas('directors.person', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['director']}%");
            });
        }
    }
}
