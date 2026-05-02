<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_expense_requires_staff_name_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('expenses.store'), [
            'category' => Expense::CATEGORY_PAYROLL,
            'amount' => 500,
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'bank_transfer',
            'description' => 'Salary',
            'staff_name' => '',
            'status' => 'unpaid',
        ]);

        $response->assertSessionHasErrors('staff_name');
        $this->assertDatabaseCount('school_expenses', 0);
    }

    public function test_payroll_expense_can_be_stored_with_staff_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('expenses.store'), [
            'category' => Expense::CATEGORY_PAYROLL,
            'amount' => 500,
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'bank_transfer',
            'description' => 'Salary',
            'staff_name' => 'A. Teacher',
            'status' => 'unpaid',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('school_expenses', [
            'category' => Expense::CATEGORY_PAYROLL,
            'staff_name' => 'A. Teacher',
            'status' => 'unpaid',
        ]);
    }

    public function test_non_admin_cannot_store_expense(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('expenses.store'), [
            'category' => Expense::CATEGORY_RUNNING,
            'amount' => 10,
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ])->assertForbidden();
    }
}
