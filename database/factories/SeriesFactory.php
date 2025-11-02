<?php
// database/factories/SeriesFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeriesFactory extends Factory
{
    protected $model = \App\Models\Series::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'synopsis' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(['ongoing', 'ended', 'upcoming']),
            'poster_url' => "https://placehold.jp/400x600.png?text=" . urlencode($this->faker->words(2, true)),
            'backdrop_url' => "https://placehold.jp/1920x1080.png?text=" . urlencode($this->faker->word()),
            'is_vip' => $this->faker->boolean(30),
            'release_date' => $this->faker->dateTimeBetween('-10 years', '+1 year'),
            'is_featured' => $this->faker->boolean(20),
            'rating' => $this->faker->randomFloat(1, 1, 10),
        ];
    }

    public function ongoing(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'ongoing',
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'ended',
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'upcoming',
        ]);
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
}
