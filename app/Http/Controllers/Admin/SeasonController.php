<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeasonController extends Controller
{
    public function index(Request $request)
    {
        $query = Season::with(['series', 'episodes' => function($q) {
            $q->orderBy('episode_number');
        }]);

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('series', function($q) use ($request) {
                      $q->where('title', 'like', '%' . $request->search . '%');
                  });
        }

        $seasons = $query->paginate(10)->withQueryString();
        $seriesList = Series::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Seasons', [
            'seasons' => $seasons,
            'seriesList' => $seriesList,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'series_id' => 'required|exists:series,id',
            'season_number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'air_date' => 'nullable|date',
            'poster_url' => 'nullable|url',
            'episodes' => 'nullable|array',
            'episodes.*.episode_number' => 'required|integer',
            'episodes.*.title' => 'required|string|max:255',
            'episodes.*.description' => 'nullable|string',
            'episodes.*.air_date' => 'nullable|date',
            'episodes.*.runtime' => 'nullable|integer',
            'episodes.*.poster_url' => 'nullable|url',
        ]);

        DB::transaction(function () use ($validated) {
            $season = Season::create(Arr::except($validated, ['episodes']));

            if (!empty($validated['episodes'])) {
                $season->episodes()->createMany($validated['episodes']);
            }
            
            $season->updateEpisodeCount();
        });

        return redirect()->back()->with('success', 'Season created successfully.');
    }

    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'series_id' => 'required|exists:series,id',
            'season_number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'air_date' => 'nullable|date',
            'poster_url' => 'nullable|url',
            'episodes' => 'nullable|array',
            'episodes.*.id' => 'nullable',
            'episodes.*.episode_number' => 'required|integer',
            'episodes.*.title' => 'required|string|max:255',
            'episodes.*.description' => 'nullable|string',
            'episodes.*.air_date' => 'nullable|date',
            'episodes.*.runtime' => 'nullable|integer',
            'episodes.*.poster_url' => 'nullable|url',
        ]);

        DB::transaction(function () use ($season, $validated) {
            $season->update(Arr::except($validated, ['episodes']));

            if (isset($validated['episodes'])) {
                // Get IDs of episodes that should remain
                $submittedIds = collect($validated['episodes'])
                    ->pluck('id')
                    ->filter(fn($id) => is_numeric($id))
                    ->toArray();

                // Delete episodes that are not in the submitted list
                $season->episodes()->whereNotIn('id', $submittedIds)->delete();

                // Update or Create episodes
                foreach ($validated['episodes'] as $epData) {
                    $data = Arr::except($epData, ['id', 'is_new']);
                    
                    if (isset($epData['id']) && is_numeric($epData['id'])) {
                        $season->episodes()->where('id', $epData['id'])->update($data);
                    } else {
                        $season->episodes()->create($data);
                    }
                }
            } else {
                if (array_key_exists('episodes', $validated) && empty($validated['episodes'])) {
                     $season->episodes()->delete();
                }
            }
            
            $season->updateEpisodeCount();
        });

        return redirect()->back()->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        $season->delete();
        return redirect()->back()->with('success', 'Season deleted successfully.');
    }
}
