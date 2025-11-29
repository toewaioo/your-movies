<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(private RatingService $ratingService) {}

    public function store(StoreRatingRequest $request): RedirectResponse
    {
        try {
            $this->ratingService->createRating($request->user(), $request->validated());

            return redirect()->back()->with('success', 'Rating submitted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateRatingRequest $request, Rating $rating): RedirectResponse
    {
        try {
            // Ensure user owns the rating
            if ($request->user()->id !== $rating->user_id) {
                return redirect()->back()->with('error', 'Unauthorized.');
            }

            $this->ratingService->updateRating($rating, $request->validated());

            return redirect()->back()->with('success', 'Rating updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Rating $rating): RedirectResponse
    {
        try {
            if (request()->user()->id !== $rating->user_id) {
                return redirect()->back()->with('error', 'Unauthorized.');
            }

            $this->ratingService->deleteRating($rating);

            return redirect()->back()->with('success', 'Rating deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
