<?php
// app/Repositories/Interfaces/RatingRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RatingRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?Rating;
    public function create(array $data): Rating;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getUserRatings(User $user, array $filters = []): LengthAwarePaginator;
    public function getUserRatingForContent(User $user, string $type, int $contentId): ?Rating;
    public function getContentRatings($content, string $type, int $perPage = 20): LengthAwarePaginator;
    public function getAverageRating($content, string $type): array;
    public function getRatingDistribution($content, string $type): array;
}
