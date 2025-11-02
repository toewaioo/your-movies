<?php
// database/factories/GenreFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class GenreFactory extends Factory
{
    protected $model = \App\Models\Genre::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}