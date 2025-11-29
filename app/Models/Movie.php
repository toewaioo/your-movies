<?php
// app/Models/Movie.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'original_title',
        'slug',
        'description',
        'release_date',
        'runtime',
        'language',
        'country',
        'imdb_id',
        'budget',
        'revenue',
        'trailer_url',
        'poster_url',
        'banner_url',
        'rating_average',
        'rating_count',
        'age_rating',
        'is_vip_only',
        'visibility_status',
        'status',
        'view_count'
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_vip_only' => 'boolean',
        'budget' => 'integer',
        'revenue' => 'integer',
        'rating_average' => 'decimal:1',
    ];

    // Relationships
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_movie');
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

    public function writers()
    {
        return $this->persons()->where('role_type', 'writer');
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

    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility_status', 'public');
    }

    public function scopeVipOnly($query)
    {
        return $query->where('is_vip_only', true);
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    // Methods
    public function updateRatingStats(): void
    {
        $this->update([
            'rating_average' => $this->ratings()->avg('rating') ?? 0,
            'rating_count' => $this->ratings()->count(),
        ]);
    }

    public function getFormattedRuntimeAttribute(): string
    {
        $hours = floor($this->runtime / 60);
        $minutes = $this->runtime % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function addView(): void
    {
        $this->view_count++;
        $this->save();
    }
}
