<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $categories = array_keys(Expense::CATEGORIES);
        $methods = array_keys(Expense::PAYMENT_METHODS);
        $statuses = array_keys(Expense::STATUSES);

        $rules = [
            'category' => ['required', 'string', Rule::in($categories)],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'payment_method' => ['required', 'string', Rule::in($methods)],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in($statuses)],
        ];

        if ($this->input('category') === Expense::CATEGORY_PAYROLL) {
            $rules['staff_name'] = ['required', 'string', 'max:255'];
            $rules['status'] = ['required', 'string', Rule::in($statuses)];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('category') !== Expense::CATEGORY_PAYROLL) {
            $this->merge([
                'staff_name' => null,
                'status' => 'paid',
            ]);
        }
    }
}
