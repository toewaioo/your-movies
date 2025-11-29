<?php
// app/Models/Season.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'season_number',
        'title',
        'description',
        'air_date',
        'episode_count'
    ];

    // protected $casts = [
    //     'air_date' => 'date',
    // ];

    // Relationships
    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function persons()
    {
        return $this->hasMany(PersonRole::class);
    }

    // Methods
    public function updateEpisodeCount(): void
    {
        $this->update(['episode_count' => $this->episodes()->count()]);
    }
}
