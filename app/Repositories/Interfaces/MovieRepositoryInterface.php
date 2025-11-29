<?php
// app/Repositories/Interfaces/MovieRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Movie;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MovieRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?Movie;
    public function findBySlug(string $slug): ?Movie;
    public function create(array $data): Movie;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query, array $filters = []): LengthAwarePaginator;
    public function getTrending(int $limit = 10): Collection;
    public function getRelated(Movie $movie, int $limit = 6): Collection;
    public function getByGenre(string $genreSlug, array $filters = []): LengthAwarePaginator;
    public function getUpcoming(array $filters = []): LengthAwarePaginator;
    public function getLatest(array $filters = []): LengthAwarePaginator;
    public function getByYear(int $year, array $filters = []): LengthAwarePaginator;
    public function incrementViewCount(int $id): bool;
}
