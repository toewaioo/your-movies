<?php
// app/Services/SeriesService.php
namespace App\Services;

use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;
use App\Repositories\Interfaces\SeriesRepositoryInterface;
use App\Events\EpisodeViewed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SeriesService
{
    public function __construct(
        private SeriesRepositoryInterface $seriesRepository
    ) {}

    public function getAllSeries(array $filters = []): LengthAwarePaginator
    {
        return $this->seriesRepository->all($filters);
    }

    public function getSeries(int $id): ?Series
    {
        return $this->seriesRepository->find($id);
    }

    public function getSeriesBySlug(string $slug): ?Series
    {
        return $this->seriesRepository->findBySlug($slug);
    }

    public function createSeries(array $data): Series
    {
        return DB::transaction(function () use ($data) {
            $series = $this->seriesRepository->create($data);

            if (isset($data['genres'])) {
                $series->genres()->sync($data['genres']);
            }

            return $series->load(['genres', 'seasons']);
        });
    }

    public function updateSeries(int $id, array $data): Series
    {
        return DB::transaction(function () use ($id, $data) {
            $series = $this->getSeries($id);

            if (!$series) {
                throw new \Exception('Series not found');
            }

            $this->seriesRepository->update($id, $data);

            if (isset($data['genres'])) {
                $series->genres()->sync($data['genres']);
            }

            return $series->fresh(['genres', 'seasons']);
        });
    }

    public function deleteSeries(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $series = $this->getSeries($id);

            if (!$series) {
                throw new \Exception('Series not found');
            }

            // Delete related data
            $series->genres()->detach();
            $series->persons()->delete();

            // Delete seasons and episodes
            foreach ($series->seasons as $season) {
                $season->episodes()->delete();
            }
            $series->seasons()->delete();

            return $this->seriesRepository->delete($id);
        });
    }

    public function searchSeries(string $query, array $filters = []): LengthAwarePaginator
    {
        return $this->seriesRepository->search($query, $filters);
    }

    public function getTrendingSeries(int $limit = 10): Collection
    {
        return $this->seriesRepository->getTrending($limit);
    }

    public function getSeason(string $seriesSlug, int $seasonNumber): ?Season
    {
        return $this->seriesRepository->getSeason($seriesSlug, $seasonNumber);
    }

    public function getEpisode(string $seriesSlug, int $seasonNumber, int $episodeNumber): ?Episode
    {
        return $this->seriesRepository->getEpisode($seriesSlug, $seasonNumber, $episodeNumber);
    }

    public function trackEpisodeView(Episode $episode): void
    {
        event(new EpisodeViewed($episode));
    }

    public function createSeason(int $seriesId, array $data): Season
    {
        return DB::transaction(function () use ($seriesId, $data) {
            $series = $this->getSeries($seriesId);

            if (!$series) {
                throw new \Exception('Series not found');
            }

            $season = $series->seasons()->create($data);

            return $season->load('episodes');
        });
    }

    public function updateSeason(int $seasonId, array $data): Season
    {
        return DB::transaction(function () use ($seasonId, $data) {
            $season = Season::find($seasonId);

            if (!$season) {
                throw new \Exception('Season not found');
            }

            $season->update($data);
            $season->updateEpisodeCount();

            return $season->fresh('episodes');
        });
    }

    public function createEpisode(int $seasonId, array $data): Episode
    {
        return DB::transaction(function () use ($seasonId, $data) {
            $season = Season::find($seasonId);

            if (!$season) {
                throw new \Exception('Season not found');
            }

            $episode = $season->episodes()->create($data);
            $season->updateEpisodeCount();

            return $episode->load(['season.series']);
        });
    }

    public function updateEpisode(int $episodeId, array $data): Episode
    {
        return DB::transaction(function () use ($episodeId, $data) {
            $episode = Episode::find($episodeId);

            if (!$episode) {
                throw new \Exception('Episode not found');
            }

            $episode->update($data);

            // Update season episode count
            $episode->season->updateEpisodeCount();

            return $episode->fresh(['season.series']);
        });
    }

    public function deleteEpisode(int $episodeId): bool
    {
        return DB::transaction(function () use ($episodeId) {
            $episode = Episode::find($episodeId);

            if (!$episode) {
                throw new \Exception('Episode not found');
            }

            $season = $episode->season;
            $episode->delete();

            // Update season episode count
            $season->updateEpisodeCount();

            return true;
        });
    }

    public function updateSeriesRating(Series $series): void
    {
        $series->updateRatingStats();
    }
}
