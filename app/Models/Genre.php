<?php
// app/Models/Genre.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'genre_movie');
    }

    public function series()
    {
        return $this->belongsToMany(Series::class, 'genre_series');
    }
    public function scopeHasContent($query)
    {
        return $query->whereHas('movies')->orWhereHas('series');
    }
}
