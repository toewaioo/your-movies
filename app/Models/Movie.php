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
        'slug',
        'synopsis',
        'release_date',
        'runtime',
        'rating',
        'poster_url',
        'backdrop_url',
        'links',
        'is_vip',
        'is_featured',
        'views',
        'director',
    ];

    protected $casts = [
        'release_date' => 'date',
        'links' => 'array',
        'is_vip' => 'boolean',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'movie_actor') // Explicit table name
            ->withPivot('character_name')
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'movie_tag') // Explicit table name
            ->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genre') // Explicit table name
            ->withTimestamps();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('synopsis', 'like', "%{$search}%")
                ->orWhere('director', 'like', "%{$search}%");
        });
    }

    public function scopeVip($query, $isVip = true)
    {
        return $query->where('is_vip', $isVip);
    }

    public function scopeReleasedAfter($query, $date)
    {
        return $query->where('release_date', '>=', $date);
    }

    public function scopeReleasedBefore($query, $date)
    {
        return $query->where('release_date', '<=', $date);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('release_date', $year);
    }

    public function incrementViews()
    {
        $this->timestamps = false;
        $this->increment('views');
        $this->timestamps = true;
    }
}
