<?php
// app/Services/PersonService.php
namespace App\Services;

use App\Models\Person;
use App\Models\PersonRole;
use App\Repositories\Interfaces\PersonRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PersonService
{
    public function __construct(
        private PersonRepositoryInterface $personRepository
    ) {}

    public function getAllPersons(array $filters = []): LengthAwarePaginator
    {
        return $this->personRepository->all($filters);
    }

    public function getPerson(int $id): ?Person
    {
        return $this->personRepository->find($id);
    }

    public function getPersonByImdbId(string $imdbId): ?Person
    {
        return $this->personRepository->findByImdbId($imdbId);
    }

    public function createPerson(array $data): Person
    {
        return DB::transaction(function () use ($data) {
            return $this->personRepository->create($data);
        });
    }

    public function updatePerson(int $id, array $data): Person
    {
        return DB::transaction(function () use ($id, $data) {
            $person = $this->getPerson($id);

            if (!$person) {
                throw new \Exception('Person not found');
            }

            $this->personRepository->update($id, $data);

            return $person->fresh();
        });
    }

    public function deletePerson(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $person = $this->getPerson($id);

            if (!$person) {
                throw new \Exception('Person not found');
            }

            // Delete all roles associated with this person
            $person->roles()->delete();

            return $this->personRepository->delete($id);
        });
    }

    public function searchPersons(string $query, array $filters = []): LengthAwarePaginator
    {
        return $this->personRepository->search($query, $filters);
    }

    public function getPersonFilmography(int $personId): array
    {
        $person = $this->getPerson($personId);

        if (!$person) {
            throw new \Exception('Person not found');
        }

        $movies = $person->movies()
            ->with(['genres'])
            ->orderBy('release_date', 'desc')
            ->get();

        $series = $person->series()
            ->with(['genres'])
            ->orderBy('release_year_start', 'desc')
            ->get();

        return [
            'movies' => $movies,
            'series' => $series,
            'total_credits' => $movies->count() + $series->count(),
        ];
    }

    public function addPersonRole(array $data): PersonRole
    {
        return DB::transaction(function () use ($data) {
            return PersonRole::create($data);
        });
    }

    public function updatePersonRole(int $roleId, array $data): PersonRole
    {
        return DB::transaction(function () use ($roleId, $data) {
            $role = PersonRole::find($roleId);

            if (!$role) {
                throw new \Exception('Role not found');
            }

            $role->update($data);

            return $role->fresh(['person', 'movie', 'series', 'season', 'episode']);
        });
    }

    public function deletePersonRole(int $roleId): bool
    {
        $role = PersonRole::find($roleId);

        if (!$role) {
            throw new \Exception('Role not found');
        }

        return $role->delete();
    }

    public function getPopularActors(int $limit = 10): Collection
    {
        return $this->personRepository->getPopularActors($limit);
    }

    public function getPersonsByType(string $type, array $filters = []): LengthAwarePaginator
    {
        return $this->personRepository->getByType($type, $filters);
    }
}
