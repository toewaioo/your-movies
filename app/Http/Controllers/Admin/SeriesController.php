<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Genre;
use App\Models\Person;
use App\Models\WatchLink;
use App\Models\DownloadLink;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Series::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $series = $query->with(['persons', 'genres', 'seasons.episodes.downloadLinks', 'seasons.episodes.watchLinks'])->latest()->paginate(10)->withQueryString();
        return Inertia::render('Admin/Series', [
            'series' => $series,
            'genres' => Genre::all(),
            'persons' => Person::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:series,slug',
            'original_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_year_start' => 'nullable|integer',
            'release_year_end' => 'nullable|integer',
            'status' => 'nullable|string',
            'language' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'trailer_url' => 'nullable|string',
            'age_rating' => 'nullable|string',
            'is_vip_only' => 'nullable|boolean',
            'rating_average' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'persons' => 'nullable|array',
            'persons.*.person_id' => 'required|exists:persons,id',
            'persons.*.role_type' => 'required|string',
            'persons.*.character_name' => 'nullable|string',
            'episode_links' => 'nullable|array',
            'episode_links.*.id' => 'nullable',
            'episode_links.*.season_number' => 'required|integer|min:1',
            'episode_links.*.episode_number' => 'required|integer|min:1',
            'episode_links.*.link_category' => 'required|in:watch,download',
            'episode_links.*.server_name' => 'nullable|string',
            'episode_links.*.url' => 'nullable|string',
            'episode_links.*.embed_code' => 'nullable|string',
            'episode_links.*.quality' => 'nullable|string',
            'episode_links.*.file_size' => 'nullable|string',
            'episode_links.*.file_format' => 'nullable|string',
            'episode_links.*.is_vip_only' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $series = Series::create($validated);

            if (isset($validated['genres'])) {
                $series->genres()->sync($validated['genres']);
            }

            if (isset($validated['persons'])) {
                foreach ($validated['persons'] as $personData) {
                    $series->persons()->create([
                        'person_id' => $personData['person_id'],
                        'role_type' => $personData['role_type'],
                        'character_name' => $personData['character_name'] ?? null,
                    ]);
                }
            }

            if (isset($validated['episode_links'])) {
                $this->syncEpisodeLinks($series, $validated['episode_links']);
            }
        });

        return redirect()->back()->with('success', 'Series created successfully.');
    }

    public function update(Request $request, Series $series)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:series,slug,' . $series->id,
            'original_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_year_start' => 'nullable|integer',
            'release_year_end' => 'nullable|integer',
            'status' => 'nullable|string',
            'language' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'banner_url' => 'nullable|string',
            'trailer_url' => 'nullable|string',
            'age_rating' => 'nullable|string',
            'is_vip_only' => 'nullable|boolean',
            'rating_average' => 'nullable|numeric',
            'rating_count' => 'nullable|integer',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'persons' => 'nullable|array',
            'persons.*.person_id' => 'required|exists:persons,id',
            'persons.*.role_type' => 'required|string',
            'persons.*.character_name' => 'nullable|string',
            'episode_links' => 'nullable|array',
            'episode_links.*.id' => 'nullable',
            'episode_links.*.season_number' => 'required|integer|min:1',
            'episode_links.*.episode_number' => 'required|integer|min:1',
            'episode_links.*.link_category' => 'required|in:watch,download',
            'episode_links.*.server_name' => 'nullable|string',
            'episode_links.*.url' => 'nullable|string',
            'episode_links.*.embed_code' => 'nullable|string',
            'episode_links.*.quality' => 'nullable|string',
            'episode_links.*.file_size' => 'nullable|string',
            'episode_links.*.file_format' => 'nullable|string',
            'episode_links.*.is_vip_only' => 'boolean',
        ]);

        DB::transaction(function () use ($series, $validated) {
            $series->update($validated);

            if (isset($validated['genres'])) {
                $series->genres()->sync($validated['genres']);
            }

            if (isset($validated['persons'])) {
                $series->persons()->delete();
                foreach ($validated['persons'] as $personData) {
                    $series->persons()->create([
                        'person_id' => $personData['person_id'],
                        'role_type' => $personData['role_type'],
                        'character_name' => $personData['character_name'] ?? null,
                    ]);
                }
            }

            if (isset($validated['episode_links'])) {
                $this->syncEpisodeLinks($series, $validated['episode_links']);
            }
        });

        return redirect()->back()->with('success', 'Series updated successfully.');
    }

    public function destroy(Series $series)
    {
        $series->delete();
        return redirect()->back()->with('success', 'Series deleted successfully.');
    }

    private function syncEpisodeLinks(Series $series, array $links)
    {
        // 1. Collect IDs of submitted links to handle deletions
        $submittedWatchIds = [];
        $submittedDownloadIds = [];

        foreach ($links as $linkData) {
            // Find or Create Season
            $season = $series->seasons()->firstOrCreate(
                ['season_number' => $linkData['season_number']],
                ['title' => 'Season ' . $linkData['season_number']]
            );

            // Find or Create Episode
            $episode = $season->episodes()->firstOrCreate(
                ['episode_number' => $linkData['episode_number']],
                ['title' => 'Episode ' . $linkData['episode_number']]
            );

            // Handle Link
            if ($linkData['link_category'] === 'watch') {
                if (empty($linkData['url']) && !empty($linkData['embed_code'])) {
                    $linkData['url'] = 'embed';
                }
                if (empty($linkData['url'])) continue;

                $data = [
                    'server_name' => $linkData['server_name'],
                    'url' => $linkData['url'],
                    'embed_code' => $linkData['embed_code'] ?? null,
                    'quality' => $linkData['quality'],
                    'is_vip_only' => $linkData['is_vip_only'] ?? false,
                    'type' => !empty($linkData['embed_code']) ? 'embed' : 'url',
                ];

                if (isset($linkData['id']) && $linkData['id']) {
                    $watchLink = WatchLink::find($linkData['id']);
                    if ($watchLink) {
                        $watchLink->update($data);
                        $submittedWatchIds[] = $watchLink->id;
                    }
                } else {
                    $newLink = $episode->watchLinks()->create($data);
                    $submittedWatchIds[] = $newLink->id;
                }

            } else {
                if (empty($linkData['url'])) continue;

                $data = [
                    'server_name' => $linkData['server_name'],
                    'url' => $linkData['url'],
                    'quality' => $linkData['quality'],
                    'file_size' => $linkData['file_size'] ?? null,
                    'file_format' => $linkData['file_format'] ?? null,
                    'is_vip_only' => $linkData['is_vip_only'] ?? false,
                ];

                if (isset($linkData['id']) && $linkData['id']) {
                    $downloadLink = DownloadLink::find($linkData['id']);
                    if ($downloadLink) {
                        $downloadLink->update($data);
                        $submittedDownloadIds[] = $downloadLink->id;
                    }
                } else {
                    $newLink = $episode->downloadLinks()->create($data);
                    $submittedDownloadIds[] = $newLink->id;
                }
            }
        }

        // 2. Delete links that are NOT in the submitted list
        // We need to get all episode IDs for this series to scope the deletion correctly
        // Note: This relies on the fact that we are sending ALL links for the series.
        // If pagination were used for links, this would be dangerous.
        // But the SeriesForm loads all links.
        $episodeIds = $series->seasons->flatMap(fn($s) => $s->episodes->pluck('id'));

        if ($episodeIds->isNotEmpty()) {
            WatchLink::whereIn('episode_id', $episodeIds)
                ->whereNotIn('id', $submittedWatchIds)
                ->delete();

            DownloadLink::whereIn('episode_id', $episodeIds)
                ->whereNotIn('id', $submittedDownloadIds)
                ->delete();
        }
    }
    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $id = $request->input('id');

        $query = Series::where('slug', $slug);
        if ($id) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
