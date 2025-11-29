<?php
// app/Models/VipSubscription.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vipKey()
    {
        return $this->belongsTo(VipKey::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('end_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<=', now());
    }

    // Methods
    public function isActive(): bool
    {
        return $this->end_date->isFuture();
    }

    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }
}
