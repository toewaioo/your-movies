<?php
// database/factories/TagFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class TagFactory extends Factory
{
    protected $model = \App\Models\Tag::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}