<?php
// app/Repositories/Interfaces/SeriesRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SeriesRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?Series;
    public function findBySlug(string $slug): ?Series;
    public function create(array $data): Series;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query, array $filters = []): LengthAwarePaginator;
    public function getTrending(int $limit = 10): Collection;
    public function getSeason(string $seriesSlug, int $seasonNumber): ?Season;
    public function getEpisode(string $seriesSlug, int $seasonNumber, int $episodeNumber): ?Episode;
    public function getByGenre(string $genreSlug, array $filters = []): LengthAwarePaginator;
    public function getByStatus(string $status, array $filters = []): LengthAwarePaginator;
    public function getLatestEpisodes(int $limit = 10): Collection;
}
