<?php
// app/Models/Tag.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    public function series()
    {
        return $this->belongsToMany(Series::class);
    }
}