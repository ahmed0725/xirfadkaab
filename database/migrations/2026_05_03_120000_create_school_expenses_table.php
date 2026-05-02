<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('payment_method', 32);
            $table->string('staff_name')->nullable();
            $table->string('status', 16)->default('paid');
            $table->timestamps();

            $table->index('expense_date');
            $table->index(['category', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_expenses');
    }
};
