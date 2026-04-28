<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fee>
 */
class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        $amount = fake()->numberBetween(50, 300);
        $paid = fake()->numberBetween(20, $amount);

        return [
            'student_id' => Student::query()->inRandomOrder()->value('id') ?? Student::factory(),
            'fee_year' => (int) now()->year,
            'fee_month' => fake()->numberBetween(1, 12),
            'amount' => $amount,
            'paid' => $paid,
            'balance' => $amount - $paid,
            'date' => fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'receipt_no' => 'RCP-' . fake()->unique()->numerify('########'),
        ];
    }
}
