<?php

use App\Models\CourseType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $type = CourseType::query()->firstOrCreate(
            ['name' => 'General skills'],
        );

        DB::table('school_classes')->whereNull('course_type_id')->update(['course_type_id' => $type->id]);
    }

    public function down(): void
    {
        //
    }
};
