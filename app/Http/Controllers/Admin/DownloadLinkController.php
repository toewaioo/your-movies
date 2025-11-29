<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DownloadLink;

class DownloadLinkController extends Controller
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
            'size' => 'nullable|string',
            'host' => 'nullable|string',
        ]);
        $link = DownloadLink::create($validated);
        return response()->json($link, 201);
    }
}
