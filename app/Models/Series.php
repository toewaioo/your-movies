<?php
// app/Models/Series.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'original_title',
        'slug',
        'description',
        'release_year_start',
        'release_year_end',
        'status',
        'language',
        'country',
        'imdb_id',
        'poster_url',
        'banner_url',
        'trailer_url',
        'age_rating',
        'is_vip_only',
        'rating_average',
        'rating_count',
        'view_count'
    ];

    protected $casts = [
        'is_vip_only' => 'boolean',
        'rating_average' => 'decimal:1',
    ];

    // Relationships
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_series');
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
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

    public function watchHistory()
    {
        return $this->hasManyThrough(WatchHistory::class, Episode::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility_status', 'public');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    // Methods
    public function getTotalEpisodesAttribute(): int
    {
        return $this->seasons->sum('episode_count');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function updateRatingStats(): void
    {
        $this->update([
            'rating_average' => $this->ratings()->avg('rating') ?? 0,
            'rating_count' => $this->ratings()->count(),
        ]);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
