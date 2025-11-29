<?php
// app/Models/Person.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'persons';

    protected $fillable = [
        'name',
        'biography',
        'birth_date',
        'death_date',
        'gender',
        'avatar_url',
        'country',
        'imdb_id',
        'place_of_birth'
    ];

    // protected $casts = [
    //     'birth_date' => 'date',
    //     'death_date' => 'date',
    // ];

    // Relationships
    public function roles()
    {
        return $this->hasMany(PersonRole::class);
    }

    public function movies()
    {
        return $this->hasManyThrough(Movie::class, PersonRole::class, 'person_id', 'id', 'id', 'movie_id');
    }

    public function series()
    {
        return $this->hasManyThrough(Series::class, PersonRole::class, 'person_id', 'id', 'id', 'series_id');
    }

    // Scopes
    public function scopeActors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('role_type', 'actor');
        });
    }

    public function scopeDirectors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('role_type', 'director');
        });
    }
}
