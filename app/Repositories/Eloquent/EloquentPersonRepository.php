<?php
// app/Repositories/Eloquent/EloquentPersonRepository.php
namespace App\Repositories\Eloquent;

use App\Models\Person;
use App\Repositories\Interfaces\PersonRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Person::with(['roles.movie', 'roles.series']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function find(int $id): ?Person
    {
        return Person::with([
            'roles.movie',
            'roles.series.seasons',
            'roles.episode.season.series'
        ])->find($id);
    }

    public function findByImdbId(string $imdbId): ?Person
    {
        return Person::where('imdb_id', $imdbId)->first();
    }

    public function create(array $data): Person
    {
        return Person::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $person = Person::find($id);

        if (!$person) {
            return false;
        }

        return $person->update($data);
    }

    public function delete(int $id): bool
    {
        $person = Person::find($id);

        if (!$person) {
            return false;
        }

        return $person->delete();
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = Person::with(['roles.movie', 'roles.series'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('biography', 'ilike', "%{$query}%");
            });

        $this->applyFilters($searchQuery, $filters);

        return $searchQuery->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getPopularActors(int $limit = 10): Collection
    {
        return Person::whereHas('roles', function ($query) {
            $query->where('role_type', 'actor');
        })
            ->withCount(['roles as movie_count' => function ($query) {
                $query->whereNotNull('movie_id');
            }])
            ->orderBy('movie_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByType(string $type, array $filters = []): LengthAwarePaginator
    {
        $query = Person::with(['roles.movie', 'roles.series'])
            ->whereHas('roles', function ($q) use ($type) {
                $q->where('role_type', $type);
            });

        $this->applyFilters($query, $filters);

        return $query->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getFilmography(int $personId): array
    {
        $person = $this->find($personId);

        if (!$person) {
            return [];
        }

        $movies = $person->movies()
            ->with(['genres'])
            ->public()
            ->released()
            ->orderBy('release_date', 'desc')
            ->get();

        $series = $person->series()
            ->with(['genres'])
            ->public()
            ->orderBy('release_year_start', 'desc')
            ->get();

        return [
            'movies' => $movies,
            'series' => $series,
            'total_credits' => $movies->count() + $series->count(),
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('role_type', $filters['role']);
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', 'ilike', "%{$filters['country']}%");
        }
    }
}
