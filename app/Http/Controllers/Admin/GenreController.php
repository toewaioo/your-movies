<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Genres', [
            'genres' => Genre::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:genres,slug',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        Genre::create($validated);
        return redirect()->back()->with('success', 'Genre created successfully.');
    }

    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:genres,slug,' . $genre->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $genre->update($validated);
        return redirect()->back()->with('success', 'Genre updated successfully.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();
        return redirect()->back()->with('success', 'Genre deleted successfully.');
    }
}
