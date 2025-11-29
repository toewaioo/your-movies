<?php
// app/Repositories/Eloquent/EloquentVIPRepository.php
namespace App\Repositories\Eloquent;

use App\Models\VipKey;
use App\Models\VipSubscription;
use App\Models\User;
use App\Repositories\Interfaces\VIPRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentVIPRepository implements VIPRepositoryInterface
{
    public function createVipKey(array $data): VipKey
    {
        return VipKey::create($data);
    }

    public function findVipKey(string $key): ?VipKey
    {
        return VipKey::where('key', $key)->first();
    }

    public function getActiveVipKeys(): Collection
    {
        return VipKey::active()->get();
    }

    public function getUserSubscription(User $user): ?VipSubscription
    {
        return VipSubscription::where('user_id', $user->id)
            ->active()
            ->first();
    }

    public function createSubscription(array $data): VipSubscription
    {
        return VipSubscription::create($data);
    }

    public function getUserSubscriptionHistory(User $user): LengthAwarePaginator
    {
        return VipSubscription::with('vipKey')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getVipStatistics(): array
    {
        $totalKeys = VipKey::count();
        $activeKeys = VipKey::active()->count();
        $usedKeys = VipKey::where('uses_count', '>', 0)->count();
        $totalSubscriptions = VipSubscription::count();
        $activeSubscriptions = VipSubscription::active()->count();

        $subscriptionsByMonth = VipSubscription::selectRaw('
            DATE_TRUNC(\'month\', created_at) as month,
            COUNT(*) as count
        ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $popularDurations = VipKey::selectRaw('
            duration_days,
            COUNT(*) as key_count,
            SUM(uses_count) as total_uses
        ')
            ->groupBy('duration_days')
            ->orderBy('total_uses', 'desc')
            ->get();

        $recentSubscriptions = VipSubscription::with(['user', 'vipKey'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'used_keys' => $usedKeys,
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'subscriptions_by_month' => $subscriptionsByMonth,
            'popular_durations' => $popularDurations,
            'recent_subscriptions' => $recentSubscriptions,
        ];
    }

    public function getExpiredSubscriptions(): Collection
    {
        return VipSubscription::expired()
            ->where('notified', false)
            ->with('user')
            ->get();
    }

    public function bulkDeactivateKeys(array $keyIds): array
    {
        $results = [
            'total' => count($keyIds),
            'deactivated' => 0,
            'failed' => 0,
        ];

        foreach ($keyIds as $keyId) {
            try {
                $key = VipKey::find($keyId);

                if ($key && $key->update(['is_active' => false])) {
                    $results['deactivated']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
