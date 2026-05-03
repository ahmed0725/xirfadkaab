@php
    $expenseModel = $expense ?? null;
@endphp
<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="category" value="Expense type" />
        <select id="expense_category" name="category" class="form-control mt-1" required>
            @foreach(\App\Models\Expense::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $expenseModel?->category) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div id="payroll_fields" class="md:col-span-2 grid gap-4 md:grid-cols-2 {{ old('category', $expenseModel?->category) === 'payroll' ? '' : 'hidden' }}">
        <div>
            <x-input-label for="staff_name" value="Staff name" />
            <input id="staff_name" name="staff_name" value="{{ old('staff_name', $expenseModel?->staff_name) }}" class="form-control mt-1" placeholder="Required for payroll">
        </div>
        <div>
            <x-input-label for="status" value="Payment status" />
            <select id="status" name="status" class="form-control mt-1">
                @foreach(\App\Models\Expense::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $expenseModel?->status ?? 'paid') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <x-input-label for="amount" value="Amount" />
        <input id="amount" type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expenseModel?->amount) }}" class="form-control mt-1" required>
    </div>
    <div>
        <x-input-label for="expense_date" value="Date" />
        <input id="expense_date" type="date" name="expense_date" value="{{ old('expense_date', optional($expenseModel?->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="form-control mt-1" required>
    </div>
    <div>
        <x-input-label for="payment_method" value="Payment method" />
        <select id="payment_method" name="payment_method" class="form-control mt-1" required>
            @foreach(\App\Models\Expense::PAYMENT_METHODS as $value => $label)
                <option value="{{ $value }}" @selected(old('payment_method', $expenseModel?->payment_method) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1" placeholder="Optional details">{{ old('description', $expenseModel?->description) }}</textarea>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cat = document.getElementById('expense_category');
        const payroll = document.getElementById('payroll_fields');
        const toggle = () => {
            if (!cat || !payroll) return;
            payroll.classList.toggle('hidden', cat.value !== 'payroll');
        };
        cat?.addEventListener('change', toggle);
        toggle();
    });
</script>
