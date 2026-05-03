<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $category = $request->input('category');
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Expense::query()
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('staff_name', 'like', "%{$search}%");
                });
            })
            ->latest('expense_date')
            ->latest('id');

        $expenses = (clone $query)->paginate(20)->withQueryString();

        $totalsQuery = Expense::query()
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('staff_name', 'like', "%{$search}%");
                });
            });

        $summary = [
            'total_amount' => (float) (clone $totalsQuery)->sum('amount'),
            'count' => (clone $totalsQuery)->count(),
            'by_category' => (clone $totalsQuery)
                ->selectRaw('category, sum(amount) as total, count(*) as cnt')
                ->groupBy('category')
                ->get()
                ->keyBy('category'),
        ];

        $filters = compact('month', 'year', 'category', 'status', 'search');

        return view('expenses.index', compact('expenses', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        Expense::create($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense): View
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
