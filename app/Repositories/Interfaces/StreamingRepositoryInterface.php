<?php
// app/Repositories/Interfaces/StreamingRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Movie;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Support\Collection;

interface StreamingRepositoryInterface
{
    public function getWatchLinksForMovie(Movie $movie, User $user, string $quality = null): Collection;
    public function getWatchLinksForEpisode(Episode $episode, User $user, string $quality = null): Collection;
    public function getDownloadLinksForMovie(Movie $movie, User $user, string $quality = null): Collection;
    public function getDownloadLinksForEpisode(Episode $episode, User $user, string $quality = null): Collection;
    public function findBestWatchLink(Movie|Episode $content, User $user, string $preferredQuality = null): ?array;
    public function updateLinkHealth(int $linkId, bool $isWorking, int $responseTime = null, string $errorMessage = null): void;
    public function getAlternativeLinks(Movie|Episode $content, string $currentServer, User $user): Collection;
    public function getLinkStatistics(): array;
}
