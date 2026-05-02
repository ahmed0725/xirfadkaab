<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $student = Student::query()->inRandomOrder()->first() ?? Student::factory()->create();

        return [
            'student_id' => $student->id,
            'school_class_id' => $student->school_class_id ?? SchoolClass::factory(),
            'subject_id' => null,
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['present', 'present', 'absent', 'late']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
