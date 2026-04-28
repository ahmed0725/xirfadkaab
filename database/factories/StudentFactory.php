<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'student_id' => 'STD-' . fake()->unique()->numerify('####'),
            'name' => fake()->name(),
            'mother_name' => fake()->name('female'),
            'phone' => fake()->numerify('061#######'),
            'age' => fake()->numberBetween(6, 17),
            'gender' => fake()->randomElement(['male', 'female']),
            'school_class_id' => SchoolClass::query()->inRandomOrder()->value('id') ?? SchoolClass::factory(),
            'status' => fake()->randomElement(['active', 'active', 'inactive']),
            'registration_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
