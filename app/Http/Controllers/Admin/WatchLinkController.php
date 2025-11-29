<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WatchLink;

class WatchLinkController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'nullable|exists:movies,id',
            'series_id' => 'nullable|exists:series,id',
            'season_id' => 'nullable|exists:seasons,id',
            'episode_id' => 'nullable|exists:episodes,id',
            'url' => 'required|string',
            'quality' => 'nullable|string',
            'host' => 'nullable|string',
        ]);
        $link = WatchLink::create($validated);
        return response()->json($link, 201);
    }
}
