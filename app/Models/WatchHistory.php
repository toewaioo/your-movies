<?php
// app/Models/WatchHistory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'movie_id',
        'episode_id',
        'last_position_seconds',
        'completed',
        'duration_seconds',
        'percent_watched'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'last_position_seconds' => 'integer',
        'duration_seconds' => 'integer',
        'percent_watched' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    // Methods
    public function updateProgress(int $position, int $duration): void
    {
        $percent = $duration > 0 ? ($position / $duration) * 100 : 0;
        $completed = $percent >= 90; // Mark as completed if watched 90% or more

        $this->update([
            'last_position_seconds' => $position,
            'duration_seconds' => $duration,
            'percent_watched' => $percent,
            'completed' => $completed,
        ]);
    }
}
