<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_fee_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('category', 32);
            $table->string('title')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->date('date');
            $table->string('receipt_no')->unique();
            $table->timestamps();

            $table->index(['student_id', 'category']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_fee_charges');
    }
};
