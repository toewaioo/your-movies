<?php
// app/Models/Review.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'movie_id',
        'episode_id',
        'title',
        'content',
        'rating',
        'contains_spoilers',
        'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'contains_spoilers' => 'boolean',
        'is_approved' => 'boolean',
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

    // public function likes()
    // {
    //     return $this->hasMany(ReviewLike::class);
    // }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeWithSpoilers($query)
    {
        return $query->where('contains_spoilers', true);
    }
}
