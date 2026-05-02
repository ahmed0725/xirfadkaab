<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Student;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('students:regenerate-ids', function () {
    $this->info('Regenerating student_id values to XIR-001, XIR-002, ...');

    $students = Student::query()
        ->orderBy('id')
        ->get(['id', 'student_id']);

    if ($students->isEmpty()) {
        $this->warn('No students found. Nothing to regenerate.');
        return self::SUCCESS;
    }

    $startIndex = 1;
    $finalPrefix = 'XIR-';

    DB::transaction(function () use ($students, $startIndex, $finalPrefix): void {
        // 1) Temporarily set unique placeholder IDs to avoid unique constraint collisions.
        foreach ($students as $student) {
            $student->update([
                'student_id' => 'TMP-' . $student->id . '-' . Str::random(8),
            ]);
        }

        // 2) Assign sequential final IDs.
        $i = 0;
        foreach ($students as $student) {
            $nextNumber = $startIndex + $i;
            $student->update([
                'student_id' => $finalPrefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
            ]);
            $i++;
        }
    });

    $this->info('Done. Updated ' . $students->count() . ' students.');
    return self::SUCCESS;
})->purpose('Backfill/normalize all student_id values to XIR-### format');
