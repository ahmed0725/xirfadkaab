<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'class_name' => 'Grade ' . fake()->unique()->numberBetween(1, 99),
            'classroom' => 'Room ' . fake()->numberBetween(1, 30),
            'monthly_fee_amount' => fake()->randomElement([80, 90, 100, 120, 150]),
        ];
    }
}
