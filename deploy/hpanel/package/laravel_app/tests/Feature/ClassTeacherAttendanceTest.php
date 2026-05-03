<?php

namespace Tests\Feature;

use App\Models\CourseType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassTeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_store_attendance_for_unassigned_class(): void
    {
        $courseType = CourseType::factory()->create();
        $assigned = SchoolClass::factory()->create(['course_type_id' => $courseType->id]);
        $other = SchoolClass::factory()->create(['course_type_id' => $courseType->id]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $assigned->teachers()->attach($teacher->id);

        $student = Student::factory()->create(['school_class_id' => $other->id]);

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'date' => now()->format('Y-m-d'),
            'school_class_id' => $other->id,
            'records' => [
                ['student_id' => $student->id, 'status' => 'present', 'note' => null],
            ],
        ])->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_teacher_can_store_attendance_for_assigned_class(): void
    {
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $schoolClass->teachers()->attach($teacher->id);

        $student = Student::factory()->create(['school_class_id' => $schoolClass->id]);

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'date' => now()->format('Y-m-d'),
            'school_class_id' => $schoolClass->id,
            'subject_id' => null,
            'records' => [
                ['student_id' => $student->id, 'status' => 'present', 'note' => null],
            ],
        ])->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'status' => 'present',
        ]);
    }

    public function test_class_data_endpoint_forbidden_for_unassigned_teacher(): void
    {
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id]);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->getJson(route('attendance.class-data', ['school_class_id' => $schoolClass->id]))
            ->assertForbidden();
    }

    public function test_class_data_returns_students_for_assigned_teacher(): void
    {
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $schoolClass->teachers()->attach($teacher->id);
        $student = Student::factory()->create(['school_class_id' => $schoolClass->id, 'name' => 'Only One Student']);

        $res = $this->actingAs($teacher)->getJson(route('attendance.class-data', ['school_class_id' => $schoolClass->id]));
        $res->assertOk();
        $res->assertJsonPath('students.0.id', $student->id);
    }

    public function test_teacher_sees_only_assigned_classes_on_index(): void
    {
        $courseType = CourseType::factory()->create();
        $mine = SchoolClass::factory()->create(['class_name' => 'MyUniqueClassX', 'course_type_id' => $courseType->id]);
        $other = SchoolClass::factory()->create(['class_name' => 'OtherClassY', 'course_type_id' => $courseType->id]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $mine->teachers()->attach($teacher->id);

        $html = $this->actingAs($teacher)->get(route('classes.index'))->assertOk()->getContent();
        $this->assertStringContainsString('MyUniqueClassX', $html);
        $this->assertStringNotContainsString('OtherClassY', $html);
    }

    public function test_school_class_computes_end_date_from_start_and_duration(): void
    {
        $courseType = CourseType::factory()->create();
        $class = SchoolClass::create([
            'class_name' => 'E2E',
            'course_type_id' => $courseType->id,
            'start_date' => '2026-01-10',
            'duration_months' => 6,
            'class_time' => '16:00:00',
            'classroom' => null,
            'monthly_fee_amount' => 100,
            'shift' => 'afternoon',
            'is_active' => true,
        ]);

        $this->assertEquals('2026-07-10', $class->fresh()->end_date->format('Y-m-d'));
    }
}
