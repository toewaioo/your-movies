<?php
// app/Models/WatchLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'episode_id',
        'quality',
        'server_name',
        'source_type',
        'url',
        'embed_code',
        'headers',
        'requires_proxy',
        'is_active',
        'is_vip_only',
        'priority',
        'success_rate',
        'last_checked_at'
    ];

    protected $casts = [
        'headers' => 'array',
        'requires_proxy' => 'boolean',
        'is_active' => 'boolean',
        'is_vip_only' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    // Relationships
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function healthChecks()
    {
        return $this->morphMany(LinkHealthCheck::class, 'linkable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, User $user)
    {
        if (!$user->isVIP()) {
            return $query->where('is_vip_only', false);
        }
        return $query;
    }

    public function scopeByQuality($query, $quality)
    {
        return $query->where('quality', $quality);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('success_rate', 'desc');
    }

    // Methods
    public function getContentAttribute()
    {
        return $this->movie ?? $this->episode;
    }

    public function markAsWorking(): void
    {
        $this->update([
            'is_active' => true,
            'success_rate' => min(100, $this->success_rate + 5),
            'last_checked_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'success_rate' => max(0, $this->success_rate - 10),
            'last_checked_at' => now(),
        ]);

        if ($this->success_rate <= 30) {
            $this->update(['is_active' => false]);
        }
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($this->is_vip_only && !$user->isVIP()) {
            return false;
        }

        return $this->is_active;
    }
}
