<?php
// app/Repositories/Interfaces/VIPRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\VipKey;
use App\Models\VipSubscription;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VIPRepositoryInterface
{
    public function createVipKey(array $data): VipKey;
    public function findVipKey(string $key): ?VipKey;
    public function getActiveVipKeys(): Collection;
    public function getUserSubscription(User $user): ?VipSubscription;
    public function createSubscription(array $data): VipSubscription;
    public function getUserSubscriptionHistory(User $user): LengthAwarePaginator;
    public function getVipStatistics(): array;
    public function getExpiredSubscriptions(): Collection;
    public function bulkDeactivateKeys(array $keyIds): array;
}
