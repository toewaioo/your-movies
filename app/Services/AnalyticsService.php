<?php
// app/Services/AnalyticsService.php
namespace App\Services;

use App\Models\Movie;
use App\Models\Series;
use App\Models\User;
use App\Models\VipSubscription;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\VipKey;

class AnalyticsService
{
    public function getDashboardStats(): array
    {
        $totalUsers = User::count();
        $totalVIPUsers = VipSubscription::active()->count();
        $totalMovies = Movie::public()->count();
        $totalSeries = Series::public()->count();

        $monthlyViews = DB::table('watch_history')
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        $recentRegistrations = User::where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            'total_users' => $totalUsers,
            'total_vip_users' => $totalVIPUsers,
            'total_movies' => $totalMovies,
            'total_series' => $totalSeries,
            'monthly_views' => $monthlyViews,
            'recent_registrations' => $recentRegistrations,
        ];
    }

    public function getTrendingMovies(int $limit = 10): array
    {
        return Movie::with(['genres'])
            ->public()
            ->released()
            ->orderBy('view_count', 'desc')
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTrendingSeries(int $limit = 10): array
    {
        return Series::with(['genres'])
            ->public()
            ->orderBy('rating_count', 'desc')
            ->orderBy('rating_average', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTopRatedMovies(int $limit = 10): array
    {
        return Movie::with(['genres'])
            ->public()
            ->released()
            ->where('rating_count', '>=', 10)
            ->orderBy('rating_average', 'desc')
            ->orderBy('rating_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getMonthlyGrowth(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $data[] = [
                'month' => $start->format('M Y'),
                'new_users' => User::whereBetween('created_at', [$start, $end])->count(),
                'new_views' => WatchHistory::whereBetween('created_at', [$start, $end])->count(),
                'new_vip_subscriptions' => VipSubscription::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        return $data;
    }

    public function getUserActivity(): array
    {
        $today = now()->today();
        $weekAgo = now()->subWeek();
        $monthAgo = now()->subMonth();

        return [
            'today' => [
                'logins' => DB::table('personal_access_tokens')
                    ->whereDate('created_at', $today)
                    ->count(),
                'views' => WatchHistory::whereDate('created_at', $today)->count(),
                'ratings' => DB::table('ratings')->whereDate('created_at', $today)->count(),
            ],
            'this_week' => [
                'logins' => DB::table('personal_access_tokens')
                    ->where('created_at', '>=', $weekAgo)
                    ->count(),
                'views' => WatchHistory::where('created_at', '>=', $weekAgo)->count(),
                'ratings' => DB::table('ratings')->where('created_at', '>=', $weekAgo)->count(),
            ],
            'this_month' => [
                'logins' => DB::table('personal_access_tokens')
                    ->where('created_at', '>=', $monthAgo)
                    ->count(),
                'views' => WatchHistory::where('created_at', '>=', $monthAgo)->count(),
                'ratings' => DB::table('ratings')->where('created_at', '>=', $monthAgo)->count(),
            ],
        ];
    }

    public function getContentPerformance(): array
    {
        $topMovies = Movie::with(['genres'])
            ->public()
            ->released()
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'view_count', 'rating_average']);

        $topSeries = Series::with(['genres'])
            ->public()
            ->orderByRaw('(SELECT SUM(view_count) FROM episodes WHERE episodes.season_id IN (SELECT id FROM seasons WHERE seasons.series_id = series.id)) DESC')
            ->limit(10)
            ->get(['id', 'title', 'rating_average']);

        $recentlyAdded = Movie::with(['genres'])
            ->public()
            ->released()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'created_at']);

        return [
            'top_movies' => $topMovies,
            'top_series' => $topSeries,
            'recently_added' => $recentlyAdded,
        ];
    }

    public function getGenreDistribution(): array
    {
        $movieGenres = DB::table('genre_movie')
            ->join('genres', 'genre_movie.genre_id', '=', 'genres.id')
            ->select('genres.name', DB::raw('COUNT(*) as count'))
            ->groupBy('genres.name')
            ->orderBy('count', 'desc')
            ->get();

        $seriesGenres = DB::table('genre_series')
            ->join('genres', 'genre_series.genre_id', '=', 'genres.id')
            ->select('genres.name', DB::raw('COUNT(*) as count'))
            ->groupBy('genres.name')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'movie_genres' => $movieGenres,
            'series_genres' => $seriesGenres,
        ];
    }

    public function getRetentionMetrics(): array
    {
        // Calculate user retention (users who logged in multiple times)
        $activeUsers = DB::table('personal_access_tokens')
            ->select('tokenable_id', DB::raw('COUNT(*) as login_count'))
            ->where('created_at', '>=', now()->subMonth())
            ->groupBy('tokenable_id')
            ->get();

        $retentionRates = [
            'single_login' => $activeUsers->where('login_count', 1)->count(),
            'multiple_logins' => $activeUsers->where('login_count', '>', 1)->count(),
            'total_active_users' => $activeUsers->count(),
        ];

        // Calculate content completion rates
        $completionStats = WatchHistory::selectRaw('
            COUNT(*) as total_views,
            SUM(CASE WHEN completed = true THEN 1 ELSE 0 END) as completed_views
        ')->first();

        return [
            'user_retention' => $retentionRates,
            'completion_rate' => $completionStats->total_views > 0 ?
                round(($completionStats->completed_views / $completionStats->total_views) * 100, 2) : 0,
            'total_views' => $completionStats->total_views,
            'completed_views' => $completionStats->completed_views,
        ];
    }

    public function getVIPMetrics(): array
    {
        $currentSubscriptions = VipSubscription::active()->count();
        $expiredSubscriptions = VipSubscription::expired()->count();
        $totalRevenue = VipKey::sum(DB::raw('uses_count * duration_days')); // Simplified revenue calculation

        $subscriptionDuration = VipSubscription::selectRaw('
            AVG(EXTRACT(EPOCH FROM (end_date - start_date)) / 86400) as avg_duration_days
        ')->first();

        return [
            'current_subscriptions' => $currentSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'total_revenue_equivalent' => $totalRevenue,
            'average_subscription_duration_days' => round($subscriptionDuration->avg_duration_days ?? 0, 2),
        ];
    }
}
