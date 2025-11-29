<?php
// app/Models/PersonRole.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'movie_id',
        'series_id',
        'season_id',
        'episode_id',
        'role_type',
        'character_name',
        'order_index'
    ];

    // Relationships
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    // Scopes
    public function scopeActors($query)
    {
        return $query->where('role_type', 'actor');
    }

    public function scopeDirectors($query)
    {
        return $query->where('role_type', 'director');
    }

    public function scopeWriters($query)
    {
        return $query->where('role_type', 'writer');
    }
}
