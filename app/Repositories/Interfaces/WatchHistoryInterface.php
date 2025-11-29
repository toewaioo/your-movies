<?php
// app/Repositories/Interfaces/WatchHistoryRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\WatchHistory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WatchHistoryRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?WatchHistory;
    public function create(array $data): WatchHistory;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getUserWatchHistory(User $user, array $filters = []): LengthAwarePaginator;
    public function getContinueWatching(User $user): Collection;
    public function updateProgress(int $id, array $data): bool;
    public function clearUserHistory(int $userId): bool;
    public function getWatchStats(User $user): array;
    public function getRecommendedContent(User $user, int $limit = 10): Collection;
}
