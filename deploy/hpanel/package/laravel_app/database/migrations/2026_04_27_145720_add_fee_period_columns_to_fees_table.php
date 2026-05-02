<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->unsignedSmallInteger('fee_year')->after('student_id');
            $table->unsignedTinyInteger('fee_month')->after('fee_year');

            $table->unique(['student_id', 'fee_year', 'fee_month'], 'fees_student_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropUnique('fees_student_period_unique');
            $table->dropColumn(['fee_year', 'fee_month']);
        });
    }
};
