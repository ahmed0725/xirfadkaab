<?php

namespace Tests\Feature;

use App\Models\CourseType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_class_deactivates_its_students(): void
    {
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id, 'is_active' => true]);
        $otherClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id, 'is_active' => true]);

        $students = Student::factory()->count(3)->create([
            'school_class_id' => $schoolClass->id,
            'status' => 'active',
        ]);
        $untouched = Student::factory()->create([
            'school_class_id' => $otherClass->id,
            'status' => 'active',
        ]);

        $schoolClass->update(['is_active' => false]);

        foreach ($students as $student) {
            $this->assertSame('inactive', $student->fresh()->status);
        }
        $this->assertSame('active', $untouched->fresh()->status);
    }

    public function test_reactivating_a_class_does_not_reactivate_students(): void
    {
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id, 'is_active' => false]);
        $student = Student::factory()->create([
            'school_class_id' => $schoolClass->id,
            'status' => 'inactive',
        ]);

        $schoolClass->update(['is_active' => true]);

        $this->assertSame('inactive', $student->fresh()->status);
    }

    public function test_class_update_via_http_deactivates_students(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courseType = CourseType::factory()->create();
        $schoolClass = SchoolClass::factory()->create(['course_type_id' => $courseType->id, 'is_active' => true]);
        $student = Student::factory()->create([
            'school_class_id' => $schoolClass->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->put(route('classes.update', $schoolClass), [
            'class_name' => $schoolClass->class_name,
            'course_type_id' => $courseType->id,
            'start_date' => $schoolClass->start_date->format('Y-m-d'),
            'duration_months' => $schoolClass->duration_months,
            'class_time' => '09:00',
            'monthly_fee_amount' => (string) $schoolClass->monthly_fee_amount,
            'shift' => 'morning',
            'is_active' => '0',
        ])->assertRedirect(route('classes.index'));

        $this->assertFalse($schoolClass->fresh()->is_active);
        $this->assertSame('inactive', $student->fresh()->status);
    }
}
