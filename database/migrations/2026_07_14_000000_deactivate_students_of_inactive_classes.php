<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: students enrolled in an inactive class must be inactive.
     * (Going forward the SchoolClass model enforces this on deactivation.)
     */
    public function up(): void
    {
        DB::table('students')
            ->whereIn('school_class_id', function ($query) {
                $query->select('id')
                    ->from('school_classes')
                    ->where('is_active', false);
            })
            ->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        // Data backfill — not reversible.
    }
};
