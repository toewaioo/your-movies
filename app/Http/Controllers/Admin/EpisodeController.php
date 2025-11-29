<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Episode;

class EpisodeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'episode_number' => 'required|integer',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
        ]);
        $episode = Episode::create($validated);
        return response()->json($episode, 201);
    }
}
