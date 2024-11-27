<?php

namespace Skaisser\LaraSendy\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Skaisser\LaraSendy\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'country' => $this->faker->country(),
        ];
    }
}
