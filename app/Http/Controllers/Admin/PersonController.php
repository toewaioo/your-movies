<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;

class PersonController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Persons', [
            'persons' => Person::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'avatar_url' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'avatar_url' => 'nullable|string',
        ]);
        // if (isset($validated['photo_url'])) {
        //     $validated['avatar_url'] = $validated['photo_url'];
        //     unset($validated['photo_url']);
        // }
        $person = Person::create($validated);

        if ($request->wantsJson()) {
            return response()->json($person, 201);
        }

        return redirect()->back()->with('success', 'Person created successfully.');
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'avatar_url' => 'nullable|string',
            'country' => 'nullable|string',
            'imdb_id' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'avatar_url' => 'nullable|string',
        ]);
        // if (isset($validated['photo_url'])) {
        //     $validated['avatar_url'] = $validated['photo_url'];
        //     unset($validated['photo_url']);
        // }
        $person->update($validated);
        return redirect()->back()->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person)
    {
        $person->delete();
        return redirect()->back()->with('success', 'Person deleted successfully.');
    }
}
