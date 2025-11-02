<?php
// database/factories/ActorFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ActorFactory extends Factory
{
    protected $model = \App\Models\Actor::class;

    public function definition(): array
    {
        //
//         'poster_url' => "https://placehold.jp/400x600.png?text=" . urlencode($this->faker->words(2, true)),
// 'backdrop_url' => "https://placehold.jp/1920x1080.png?text=" . urlencode($this->faker->word()),
         return [
            'name' => $this->faker->name(),
            'bio' => $this->faker->paragraphs(3, true),
            'profile_url' =>  "https://placehold.jp/400x600.png?text=" . urlencode($this->faker->words(4, true)),
        ];
    }

    public function withShortBio(): static
    {
        return $this->state(fn(array $attributes) => [
            'bio' => $this->faker->sentence(10),
        ]);
    }

    public function withoutProfile(): static
    {
        return $this->state(fn(array $attributes) => [
            'profile_url' => null,
        ]);
    }
}
