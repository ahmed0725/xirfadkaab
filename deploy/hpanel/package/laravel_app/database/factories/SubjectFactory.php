<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(),
            'subject_name' => fake()->randomElement(['Mathematics', 'English', 'Science', 'History', 'Geography', 'Arabic', 'ICT']),
        ];
    }
}
