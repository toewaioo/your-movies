<?php
// app/Repositories/Interfaces/PersonRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PersonRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?Person;
    public function findByImdbId(string $imdbId): ?Person;
    public function create(array $data): Person;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query, array $filters = []): LengthAwarePaginator;
    public function getPopularActors(int $limit = 10): Collection;
    public function getByType(string $type, array $filters = []): LengthAwarePaginator;
    public function getFilmography(int $personId): array;
}
