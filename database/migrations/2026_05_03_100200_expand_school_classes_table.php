<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique(['class_name']);
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('course_type_id')->nullable()->after('class_name')->constrained()->nullOnDelete();
            $table->date('start_date')->nullable()->after('course_type_id');
            $table->unsignedSmallInteger('duration_months')->nullable()->after('start_date');
            $table->date('end_date')->nullable()->after('duration_months');
            $table->time('class_time')->nullable()->after('end_date');
        });

        $now = Carbon::today();
        $end = $now->copy()->addMonths(12);

        DB::table('school_classes')->orderBy('id')->chunkById(100, function ($rows) use ($now, $end): void {
            foreach ($rows as $row) {
                DB::table('school_classes')->where('id', $row->id)->update([
                    'start_date' => $now->toDateString(),
                    'duration_months' => 12,
                    'end_date' => $end->toDateString(),
                    'class_time' => '09:00:00',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['course_type_id']);
            $table->dropColumn([
                'course_type_id',
                'start_date',
                'duration_months',
                'end_date',
                'class_time',
            ]);
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->unique('class_name');
        });
    }
};
