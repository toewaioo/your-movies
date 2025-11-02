<?php
// database/factories/EpisodeFactory.php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    protected $model = \App\Models\Episode::class;

    public function definition(): array
    {
        $releaseDate = $this->faker->dateTimeBetween('-5 years', 'now');

        return [
            'series_id' => Series::factory(),
            'season' => $this->faker->numberBetween(1, 5),
            'episode_number' => $this->faker->numberBetween(1, 20),
            'title' => $this->faker->sentence(4),
            'synopsis' => $this->faker->paragraphs(2, true),
            'runtime' => $this->faker->numberBetween(20, 60),
            'release_date' => $releaseDate,
            'links' => $this->generateLinks(),
        ];
    }

    public function forSeries(Series $series): static
    {
        return $this->state(fn(array $attributes) => [
            'series_id' => $series->id,
        ]);
    }

    public function season(int $season): static
    {
        return $this->state(fn(array $attributes) => [
            'season' => $season,
        ]);
    }

    public function episode(int $episode): static
    {
        return $this->state(fn(array $attributes) => [
            'episode_number' => $episode,
        ]);
    }

    private function generateLinks(): array
    {
        $qualities = ['1080p', '720p', '480p'];
        $platforms = ['GoogleDrive', 'AWS', 'Vimeo', 'YouTube', 'DailyMotion'];
        $types = ['stream', 'download'];

        $streamLinks = [];
        $downloadLinks = [];
        $count = $this->faker->numberBetween(2, 6);

        for ($i = 0; $i < $count; $i++) {
            $type = $this->faker->randomElement($types);
            $link = [
                'quality' => $this->faker->randomElement($qualities),
                'name' => $this->faker->randomElement(['Full HD', 'HD', 'SD']),
                'platform' => $this->faker->randomElement($platforms),
                'link' => $this->faker->url(),
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
