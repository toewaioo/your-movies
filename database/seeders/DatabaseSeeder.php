<?php
// database/seeders/DatabaseSeeder.php (Super Simple Version)

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Carbon\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear database
        $this->clearDatabase();

        // Create basic data
        $this->createUsers();
        $this->createGenres();
        $this->createTags();
        $this->createActors();
        $this->createMovies();
        $this->createSeries();
    }

    private function clearDatabase(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('movie_actor')->truncate();
        DB::table('series_actor')->truncate();
        DB::table('movie_tag')->truncate();
        DB::table('series_tag')->truncate();
        DB::table('movie_genre')->truncate();
        DB::table('series_genre')->truncate();

        Episode::truncate();
        Movie::truncate();
        Series::truncate();
        Actor::truncate();
        Tag::truncate();
        Genre::truncate();
        User::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createUsers(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@movies.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory(5)->create();
    }

    private function createGenres(): void
    {
        $genres = [
            ['name' => 'Action', 'slug' => 'action'],
            ['name' => 'Comedy', 'slug' => 'comedy'],
            ['name' => 'Drama', 'slug' => 'drama'],
            ['name' => 'Horror', 'slug' => 'horror'],
            ['name' => 'Sci-Fi', 'slug' => 'sci-fi'],
            ['name' => 'Romance', 'slug' => 'romance'],
            ['name' => 'Thriller', 'slug' => 'thriller'],
            ['name' => 'Fantasy', 'slug' => 'fantasy'],
        ];

        foreach ($genres as $genre) {
            Genre::create($genre);
        }
    }

    private function createTags(): void
    {
        $tags = [
            ['name' => 'Blockbuster', 'slug' => 'blockbuster'],
            ['name' => 'Award Winning', 'slug' => 'award-winning'],
            ['name' => 'Independent', 'slug' => 'independent'],
            ['name' => 'Classic', 'slug' => 'classic'],
            ['name' => 'New Release', 'slug' => 'new-release'],
            ['name' => 'Critically Acclaimed', 'slug' => 'critically-acclaimed'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }

    private function createActors(): void
    {
        Actor::factory(20)->create();
    }

    private function createMovies(): void
    {
        $movies = Movie::factory(25)->create();
        $genres = Genre::all();
        $tags = Tag::all();
        $actors = Actor::all();

        foreach ($movies as $movie) {
            // Attach genres (1-2)
            $movie->genres()->attach(
                $genres->random(rand(1, 2))->pluck('id')
            );

            // Attach tags (1-3)
            $movie->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')
            );

            // Attach actors (2-4)
            $selectedActors = $actors->random(rand(2, 4));
            foreach ($selectedActors as $actor) {
                $movie->actors()->attach($actor->id, [
                    'character_name' => fake()->firstName() . ' ' . fake()->lastName()
                ]);
            }
        }
    }

    private function createSeries(): void
    {
        $series = Series::factory(20)->create();
        $genres = Genre::all();
        $tags = Tag::all();
        $actors = Actor::all();

        foreach ($series as $s) {
            // Create episodes
            $seasons = rand(1, 3);
            for ($season = 1; $season <= $seasons; $season++) {
                $episodes = rand(3, 8);
                for ($episode = 1; $episode <= $episodes; $episode++) {
                    Episode::create([
                        'series_id' => $s->id,
                        'season' => $season,
                        'episode_number' => $episode,
                        'title' => "S{$season}E{$episode}: " . fake()->words(3, true),
                        'synopsis' => fake()->paragraph(),
                        'runtime' => rand(20, 60),

                        'release_date' => now()->subDays(rand(1, 365)),
                        'links' => $this->generateLinks(),
                    ]);
                }
            }


            // Attach genres (1-2)
            $s->genres()->attach(
                $genres->random(rand(1, 2))->pluck('id')
            );

            // Attach tags (1-3)
            $s->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')
            );

            // Attach actors (2-4)
            $selectedActors = $actors->random(rand(2, 4));
            foreach ($selectedActors as $actor) {
                $s->actors()->attach($actor->id, [
                    'character_name' => fake()->firstName() . ' ' . fake()->lastName()
                ]);
            }
        }
    }
    private function generateLinks(): array
    {
        $generator = \Faker\Factory::create();
        $qualities = ['1080p', '720p', '480p'];
        $platforms = ['GoogleDrive', 'AWS', 'Vimeo', 'YouTube', 'DailyMotion'];
        $types = ['stream', 'download'];

        $streamLinks = [];
        $downloadLinks = [];
        $count = $generator->numberBetween(2, 6);

        for ($i = 0; $i < $count; $i++) {
            $type = $generator->randomElement($types);
            $link = [
                'quality' => $generator->randomElement($qualities),
                'name' => $generator->randomElement(['Full HD', 'HD', 'SD']),
                'platform' => $generator->randomElement($platforms),
                'link' => $generator->url(),
                'type' => $type,
                'label' => $type === 'stream' ? 'Stream' : 'Download',
            ];
            if ($type === 'stream') {
                $streamLinks[] = $link;
            } else {
                $downloadLinks[] = $link;
            }
        }

        return [
            'stream' => $streamLinks,
            'download' => $downloadLinks,
        ];
    }
}
