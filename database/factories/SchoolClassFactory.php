<?php

namespace Database\Factories;

use App\Models\CourseType;
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
            'class_name' => 'Cohort '.fake()->unique()->numerify('####'),
            'course_type_id' => CourseType::factory(),
            'start_date' => now()->startOfDay()->toDateString(),
            'duration_months' => fake()->randomElement([6, 12]),
            'class_time' => fake()->randomElement(['09:00:00', '14:00:00', '16:00:00']),
            'classroom' => 'Room '.fake()->numberBetween(1, 30),
            'monthly_fee_amount' => fake()->randomElement([80, 90, 100, 120, 150]),
            'shift' => fake()->randomElement(SchoolClass::SHIFTS),
            'is_active' => true,
        ];
    }
}
