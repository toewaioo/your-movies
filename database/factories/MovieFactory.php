<?php
// database/factories/MovieFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieFactory extends Factory
{
    protected $model = \App\Models\Movie::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $releaseDate = $this->faker->dateTimeBetween('-10 years', '+1 year');

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'synopsis' => $this->faker->paragraphs(3, true),
            'release_date' => $releaseDate,
            'runtime' => $this->faker->numberBetween(60, 180),
            'rating' => $this->faker->randomFloat(1, 1, 10),
            'poster_url' => "https://placehold.jp/400x600.png?text=" . urlencode($this->faker->words(2, true)),
            'backdrop_url' => "https://placehold.jp/1920x1080.png?text=" . urlencode($this->faker->word()),
            'links' => $this->generateLinks(),
            'is_featured' => $this->faker->boolean(20),
            'is_vip' => $this->faker->boolean(30),
            'views' => $this->faker->numberBetween(0, 1000000),
            'director' => $this->faker->name(),
        ];
    }

    public function vip(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_vip' => true,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_vip' => false,
        ]);
    }

    public function highRated(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 7, 10),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn(array $attributes) => [
            'release_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn(array $attributes) => [
            'release_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
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
