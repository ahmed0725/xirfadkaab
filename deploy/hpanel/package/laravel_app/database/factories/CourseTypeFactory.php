<?php

namespace Database\Factories;

use App\Models\CourseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseType>
 */
class CourseTypeFactory extends Factory
{
    protected $model = CourseType::class;

    public function definition(): array
    {
        return [
            'name' => 'Skill ' . fake()->unique()->numerify('####'),
        ];
    }
}
