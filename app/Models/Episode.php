<?php
// app/Models/Episode.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'description',
        'runtime',
        'air_date',
        'view_count',
        'poster_url',
        'trailer_url',
        'imdb_id',
        'rating_average',
        'rating_count'
    ];

    protected $casts = [
        'air_date' => 'date',
        'rating_average' => 'decimal:1',
    ];

    // Relationships
    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function series()
    {
        return $this->hasOneThrough(Series::class, Season::class, 'id', 'id', 'season_id', 'series_id');
    }

    public function watchLinks()
    {
        return $this->hasMany(WatchLink::class);
    }

    public function downloadLinks()
    {
        return $this->hasMany(DownloadLink::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function persons()
    {
        return $this->hasMany(PersonRole::class);
    }

    public function actors()
    {
        return $this->persons()->where('role_type', 'actor');
    }

    public function directors()
    {
        return $this->persons()->where('role_type', 'director');
    }

    // Methods
    public function getFullTitleAttribute(): string
    {
        return "S" . str_pad($this->season->season_number, 2, '0', STR_PAD_LEFT) .
            "E" . str_pad($this->episode_number, 2, '0', STR_PAD_LEFT) .
            " - {$this->title}";
    }

    // ... other methods ...

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    // Alternative method if you prefer direct update
    public function addView(): void
    {
        $this->view_count++;
        $this->save();
    }

    public function updateRatingStats(): void
    {
        $this->update([
            'rating_average' => $this->ratings()->avg('rating') ?? 0,
            'rating_count' => $this->ratings()->count(),
        ]);
    }
}
