<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'category' => Expense::CATEGORY_RUNNING,
            'amount' => fake()->randomFloat(2, 10, 500),
            'expense_date' => fake()->date(),
            'description' => fake()->optional()->sentence(),
            'payment_method' => 'cash',
            'staff_name' => null,
            'status' => 'paid',
        ];
    }
}
