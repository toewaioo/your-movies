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
        'slug',
        'synopsis',
        'status',
        'poster_url',
        'backdrop_url',
        'is_vip',
        'is_featured',
        'rating'
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'float',
    ];



    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'series_actor') // Explicit table name
            ->withPivot('character_name')
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'series_tag') // Explicit table name
            ->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'series_genre') // Explicit table name
            ->withTimestamps();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('synopsis', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeVip($query, $isVip = true)
    {
        return $query->where('is_vip', $isVip);
    }

    public function getLatestEpisodeAttribute()
    {
        return $this->episodes()->latest('release_date')->first();
    }
    // Optional: Group episodes by season
    public function getSeasonsAttribute()
    {
        // Group episodes by season number
        return $this->episodes->groupBy('season')->map(function ($episodes, $season) {
            return [
                'number' => $season,
                'episodes' => $episodes->values(),
                'id' => $season, // for React key
                'episode_count' => $episodes->count(),
            ];
        })->values();
    }
}
