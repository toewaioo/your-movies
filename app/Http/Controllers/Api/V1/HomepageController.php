<?php
// app/Http/Controllers/Api/V1/HomepageController.php

namespace App\Http\Controllers\Api\V1;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class HomepageController extends Controller
{
    /**
     * Show homepage data: movies, series, featured.
     */
    public function index(): JsonResponse
    {
        $movies = Movie::latest()->take(10)->get();
        $series = Series::latest()->take(10)->get();
        $featured = [
            'movies' => Movie::where('is_vip', true)->take(5)->get(),
            'series' => Series::where('is_vip', true)->take(5)->get(),
        ];

        
        return response()->json([
            'movies' => $movies,
            'series' => $series,
            'featured' => $featured,
        ]);
    }
}
