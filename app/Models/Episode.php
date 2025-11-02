<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Episode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'series_id',
        'season',
        'episode_number',
        'title',
        'synopsis',
        'runtime',
        'release_date',
        'links'
    ];

    protected $casts = [
        'links' => 'array',
        'release_date' => 'date',
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
