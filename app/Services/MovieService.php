<?php
// app/Services/MovieService.php
namespace App\Services;

use App\Models\Movie;
use App\Repositories\Interfaces\MovieRepositoryInterface;
use App\Events\MovieViewed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection; // Add this import
use Illuminate\Support\Facades\DB;

class MovieService
{
    public function __construct(
        private MovieRepositoryInterface $movieRepository
    ) {}

    public function getMovies(array $filters = []): LengthAwarePaginator
    {
        return $this->movieRepository->all($filters);
    }

    public function getMovie(int $id): ?Movie
    {
        return $this->movieRepository->find($id);
    }

    public function getMovieBySlug(string $slug): ?Movie
    {
        return $this->movieRepository->findBySlug($slug);
    }

    public function createMovie(array $data): Movie
    {
        return DB::transaction(function () use ($data) {
            $movie = $this->movieRepository->create($data);

            // Attach genres if provided
            if (isset($data['genres'])) {
                $movie->genres()->sync($data['genres']);
            }

            return $movie->load(['genres', 'actors.person', 'directors.person']);
        });
    }

    public function updateMovie(int $id, array $data): Movie
    {
        return DB::transaction(function () use ($id, $data) {
            $movie = $this->getMovie($id);

            if (!$movie) {
                throw new \Exception('Movie not found');
            }

            $this->movieRepository->update($id, $data);

            // Sync genres if provided
            if (isset($data['genres'])) {
                $movie->genres()->sync($data['genres']);
            }

            return $movie->fresh(['genres', 'actors.person', 'directors.person']);
        });
    }

    public function deleteMovie(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $movie = $this->getMovie($id);

            if (!$movie) {
                throw new \Exception('Movie not found');
            }

            // Detach all relationships
            $movie->genres()->detach();
            $movie->persons()->delete();
            $movie->watchLinks()->delete();
            $movie->downloadLinks()->delete();

            return $this->movieRepository->delete($id);
        });
    }

    public function searchMovies(string $query, array $filters = []): LengthAwarePaginator
    {
        return $this->movieRepository->search($query, $filters);
    }

    public function trackView(Movie $movie): void
    {
        event(new MovieViewed($movie));
    }

    public function getTrendingMovies(int $limit = 10): Collection // Changed to Collection
    {
        return $this->movieRepository->getTrending($limit);
    }

    public function getRelatedMovies(Movie $movie, int $limit = 6): Collection // Changed to Collection
    {
        return $this->movieRepository->getRelated($movie, $limit);
    }

    public function getMoviesByGenre(string $genreSlug, array $filters = []): LengthAwarePaginator
    {
        $filters['genre'] = $genreSlug;
        return $this->movieRepository->all($filters);
    }

    public function updateMovieRating(Movie $movie): void
    {
        $movie->updateRatingStats();
    }

    public function addPersonToMovie(Movie $movie, array $personData): void
    {
        DB::transaction(function () use ($movie, $personData) {
            $movie->persons()->create($personData);
        });
    }

    public function removePersonFromMovie(Movie $movie, int $personRoleId): void
    {
        DB::transaction(function () use ($movie, $personRoleId) {
            $movie->persons()->where('id', $personRoleId)->delete();
        });
    }
}
