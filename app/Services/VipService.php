<?php
// app/Services/VIPService.php
namespace App\Services;

use App\Models\VipKey;
use App\Models\VipSubscription;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VIPService
{
    public function generateVipKeys(int $count, int $durationDays, ?int $maxUses = null, ?Carbon $expiresAt = null): array
    {
        $keys = [];

        DB::transaction(function () use ($count, $durationDays, $maxUses, $expiresAt, &$keys) {
            for ($i = 0; $i < $count; $i++) {
                $key = Str::upper(Str::random(16));

                $vipKey = VipKey::create([
                    'key' => $key,
                    'duration_days' => $durationDays,
                    'max_uses' => $maxUses,
                    'expires_at' => $expiresAt,
                    'is_active' => true,
                ]);

                $keys[] = $vipKey;
            }
        });

        return $keys;
    }

    public function getUserSubscription(User $user): ?VipSubscription
    {
        return VipSubscription::where('user_id', $user->id)
            ->active()
            ->first();
    }

    public function getUserSubscriptionHistory(User $user)
    {
        return VipSubscription::with('vipKey')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function deactivateKey(string $key): bool
    {
        $vipKey = VipKey::where('key', $key)->first();

        if (!$vipKey) {
            throw new \Exception('VIP key not found');
        }

        return $vipKey->update(['is_active' => false]);
    }

    public function extendSubscription(User $user, int $additionalDays): VipSubscription
    {
        return DB::transaction(function () use ($user, $additionalDays) {
            $subscription = $this->getUserSubscription($user);

            if (!$subscription) {
                throw new \Exception('No active subscription found');
            }

            $newEndDate = $subscription->end_date->addDays($additionalDays);
            $subscription->update(['end_date' => $newEndDate]);

            return $subscription->fresh();
        });
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

        return [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'used_keys' => $usedKeys,
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'subscriptions_by_month' => $subscriptionsByMonth,
            'popular_durations' => $popularDurations,
        ];
    }

    public function checkExpiredSubscriptions(): array
    {
        $expiredSubscriptions = VipSubscription::expired()
            ->where('notified', false)
            ->get();

        $results = [
            'checked' => 0,
            'expired' => 0,
            'notified' => 0,
        ];

        foreach ($expiredSubscriptions as $subscription) {
            $results['checked']++;
            $results['expired']++;

            // Here you would typically send a notification
            // For now, we'll just mark as notified
            $subscription->update(['notified' => true]);
            $results['notified']++;
        }

        return $results;
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
