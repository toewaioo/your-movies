<?php
// app/Models/Actor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
        'profile_url',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, "movie_actor") // Explicit table name
            ->withPivot('character_name')
            ->withTimestamps();
    }

    public function series()
    {
        return $this->belongsToMany(Series::class, 'series_actor') // Explicit table name
            ->withPivot('character_name')
            ->withTimestamps();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
