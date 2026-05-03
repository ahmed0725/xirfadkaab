<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\CourseType;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCourseType = CourseType::query()->firstOrCreate(
            ['name' => 'General skills'],
        );

        $classNames = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
        $classes = collect($classNames)->map(function (string $className, int $index) use ($defaultCourseType) {
            $shifts = ['morning', 'afternoon', 'evening'];

            return SchoolClass::create([
                'class_name' => $className,
                'course_type_id' => $defaultCourseType->id,
                'start_date' => now()->startOfDay()->toDateString(),
                'duration_months' => 12,
                'class_time' => sprintf('%02d:00:00', 8 + ($index % 3)),
                'classroom' => 'Room '.($index + 1),
                'monthly_fee_amount' => [80, 90, 100, 110, 120, 130][$index],
                'shift' => $shifts[$index % 3],
                'is_active' => true,
            ]);
        });
        $subjectPool = ['Mathematics', 'English', 'Science', 'History', 'Geography', 'ICT', 'Civics', 'Arabic'];

        foreach ($classes as $class) {
            collect($subjectPool)
                ->shuffle()
                ->take(5)
                ->each(fn ($subjectName) => Subject::create([
                    'school_class_id' => $class->id,
                    'subject_name' => $subjectName,
                ]));
        }

        $students = collect();

        // Create students sequentially so XIR-### generation can safely increment.
        foreach (range(1, 24) as $_) {
            $class = $classes->random();

            $students->push(Student::factory()->create([
                'school_class_id' => $class->id,
            ]));
        }

        foreach ($students as $student) {
            $studentSubjects = Subject::query()->where('school_class_id', $student->school_class_id)->pluck('id');

            for ($i = 0; $i < 8; $i++) {
                Attendance::create([
                    'student_id' => $student->id,
                    'school_class_id' => $student->school_class_id,
                    'subject_id' => $studentSubjects->random(),
                    'date' => now()->subDays($i + 1)->toDateString(),
                    'status' => collect(['present', 'present', 'absent', 'late'])->random(),
                    'note' => fake()->optional()->sentence(),
                ]);
            }

            for ($i = 0; $i < 3; $i++) {
                // Use a fresh copy to avoid any chance of date-mutation affecting month calculations.
                $monthDate = now()->copy()->startOfMonth()->subMonths($i);
                $amount = (float) $student->schoolClass->monthly_fee_amount;
                $paid = rand(0, (int) $amount);

                Fee::create([
                    'student_id' => $student->id,
                    'fee_year' => (int) $monthDate->format('Y'),
                    'fee_month' => (int) $monthDate->format('m'),
                    'amount' => $amount,
                    'paid' => $paid,
                    'balance' => $amount - $paid,
                    'date' => $monthDate->copy()->day(rand(1, 28))->toDateString(),
                    'receipt_no' => 'RCP-'.now()->format('Ymd').'-'.$student->id.'-'.$i.'-'.rand(100, 999),
                ]);
            }
        }
    }
}
