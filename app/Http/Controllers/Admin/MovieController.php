<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Person;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $movies = $query->with(['genres', 'persons', 'downloadLinks', 'watchLinks'])->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Movies', [
            'movies' => $movies,
            'genres' => Genre::all(),
            'persons' => Person::all(), // In a real app, use async search
        ]);
    }

    public function store(Request $request)
    {
       

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|max:255|unique:movies,slug',
            'original_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'runtime' => 'nullable|integer',
            'language' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'budget' => 'nullable|integer',
            'revenue' => 'nullable|integer',
            'trailer_url' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'rating_average' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'age_rating' => 'nullable|string',
            'is_vip_only' => 'nullable|boolean',
            'visibility_status' => 'nullable|string',
            'status' => 'nullable|string',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'persons' => 'nullable|array',
            'persons.*.person_id' => 'required|exists:persons,id',
            'persons.*.role_type' => 'required|string',
            'persons.*.character_name' => 'nullable|string',
            'watch_links' => 'nullable|array',
            'watch_links.*.server_name' => 'nullable|string',
            'watch_links.*.url' => 'nullable|string',
            'watch_links.*.embed_code' => 'nullable|string',
            'watch_links.*.quality' => 'nullable|string',
            'watch_links.*.is_vip_only' => 'boolean',
            'download_links' => 'nullable|array',
            'download_links.*.server_name' => 'nullable|string',
            'download_links.*.url' => 'nullable|string',
            'download_links.*.quality' => 'nullable|string',
            'download_links.*.file_size' => 'nullable|string',
            'download_links.*.file_format' => 'nullable|string',
            'download_links.*.is_vip_only' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $movie = Movie::create($validated);

            if (isset($validated['genres'])) {
                $movie->genres()->sync($validated['genres']);
            }

            if (isset($validated['persons'])) {
                foreach ($validated['persons'] as $personData) {
                    $movie->persons()->create([
                        'person_id' => $personData['person_id'],
                        'role_type' => $personData['role_type'],
                        'character_name' => $personData['character_name'] ?? null,
                    ]);
                }
            }

            $this->syncLinks($movie, $validated);
        });

        return redirect()->back()->with('success', 'Movie created successfully.');
    }

    public function update(Request $request, Movie $movie)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:movies,slug,' . $movie->id,
            'original_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'runtime' => 'nullable|integer',
            'language' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'budget' => 'nullable|integer',
            'revenue' => 'nullable|integer',
            'trailer_url' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'rating_average' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'age_rating' => 'nullable|string',
            'is_vip_only' => 'nullable|boolean',
            'visibility_status' => 'nullable|string',
            'status' => 'nullable|string',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'persons' => 'nullable|array',
            'persons.*.person_id' => 'required|exists:persons,id',
            'persons.*.role_type' => 'required|string',
            'persons.*.character_name' => 'nullable|string',
            'watch_links' => 'nullable|array',
            'watch_links.*.id' => 'nullable|integer',
            'watch_links.*.server_name' => 'nullable|string',
            'watch_links.*.url' => 'nullable|string',
            'watch_links.*.embed_code' => 'nullable|string',
            'watch_links.*.quality' => 'nullable|string',
            'watch_links.*.is_vip_only' => 'boolean',
            'download_links' => 'nullable|array',
            'download_links.*.id' => 'nullable|integer',
            'download_links.*.server_name' => 'nullable|string',
            'download_links.*.url' => 'nullable|string',
            'download_links.*.quality' => 'nullable|string',
            'download_links.*.file_size' => 'nullable|string',
            'download_links.*.file_format' => 'nullable|string',
            'download_links.*.is_vip_only' => 'boolean',
        ]);

        DB::transaction(function () use ($movie, $validated) {
            $movie->update($validated);

            if (isset($validated['genres'])) {
                $movie->genres()->sync($validated['genres']);
            }

            if (isset($validated['persons'])) {
                $movie->persons()->delete(); // Remove old roles
                foreach ($validated['persons'] as $personData) {
                    $movie->persons()->create([
                        'person_id' => $personData['person_id'],
                        'role_type' => $personData['role_type'],
                        'character_name' => $personData['character_name'] ?? null,
                    ]);
                }
            }

            $this->syncLinks($movie, $validated);
        });

        return redirect()->back()->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->back()->with('success', 'Movie deleted successfully.');
    }

    private function syncLinks(Movie $movie, array $data)
    {
        // Sync Watch Links
        if (isset($data['watch_links'])) {
            $currentIds = collect($data['watch_links'])->pluck('id')->filter()->toArray();
            $movie->watchLinks()->whereNotIn('id', $currentIds)->delete();

            foreach ($data['watch_links'] as $linkData) {
                // Ensure URL is not empty if embed code is present
                if (empty($linkData['url']) && !empty($linkData['embed_code'])) {
                    $linkData['url'] = 'embed';
                }

                if (empty($linkData['url'])) continue; // Skip invalid links

                $movie->watchLinks()->updateOrCreate(
                    ['id' => $linkData['id'] ?? null],
                    [
                        'server_name' => $linkData['server_name'],
                        'url' => $linkData['url'],
                        'embed_code' => $linkData['embed_code'] ?? null,
                        'quality' => $linkData['quality'],
                        'is_vip_only' => $linkData['is_vip_only'] ?? false,
                        'type' => !empty($linkData['embed_code']) ? 'embed' : 'url', // Optional, if you have a type column
                    ]
                );
            }
        }

        // Sync Download Links
        if (isset($data['download_links'])) {
            $currentIds = collect($data['download_links'])->pluck('id')->filter()->toArray();
            $movie->downloadLinks()->whereNotIn('id', $currentIds)->delete();

            foreach ($data['download_links'] as $linkData) {
                if (empty($linkData['url'])) continue;

                $movie->downloadLinks()->updateOrCreate(
                    ['id' => $linkData['id'] ?? null],
                    [
                        'server_name' => $linkData['server_name'],
                        'url' => $linkData['url'],
                        'quality' => $linkData['quality'],
                        'file_size' => $linkData['file_size'] ?? null,
                        'file_format' => $linkData['file_format'] ?? null,
                        'is_vip_only' => $linkData['is_vip_only'] ?? false,
                    ]
                );
            }
        }
    }

    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $id = $request->input('id');

        $query = Movie::where('slug', $slug);
        if ($id) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
