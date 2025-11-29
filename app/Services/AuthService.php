<?php
// app/Services/AuthService.php
namespace App\Services;

use App\Models\User;
use App\Models\VipKey;
use App\Models\VipSubscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            return $user;
        });
    }

    public function login(array $credentials): ?array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if ($user->is_banned) {
            throw new \Exception('Account has been banned. Please contact support.');
        }

        if (!$user->hasVerifiedEmail()) {
            throw new \Exception('Please verify your email address before logging in.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        if ($token) {
            // Delete via the tokens relation (query) to avoid calling delete on an unexpected type
            $user->tokens()->where('id', $token->id)->delete();
        }
    }

    public function redeemVipKey(User $user, string $key): bool
    {
        return DB::transaction(function () use ($user, $key) {
            $vipKey = VipKey::active()
                ->where('key', $key)
                ->first();

            if (!$vipKey) {
                throw new \Exception('Invalid or expired VIP key');
            }

            // Check if user already has active subscription
            $activeSubscription = VipSubscription::where('user_id', $user->id)
                ->active()
                ->exists();

            if ($activeSubscription) {
                throw new \Exception('You already have an active VIP subscription');
            }

            // Calculate end date based on key duration
            $startDate = now();
            $endDate = now()->addDays($vipKey->duration_days);

            // Create subscription
            VipSubscription::create([
                'user_id' => $user->id,
                'key_id' => $vipKey->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Increment key usage
            $vipKey->incrementUses();

            // Deactivate key if max uses reached
            if ($vipKey->max_uses && $vipKey->uses_count >= $vipKey->max_uses) {
                $vipKey->update(['is_active' => false]);
            }

            return true;
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'avatar_url' => $data['avatar_url'] ?? $user->avatar_url,
        ]);

        return $user->fresh();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return true;
    }
}
