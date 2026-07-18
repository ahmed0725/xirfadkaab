<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Add inventory item</h2></x-slot>
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('inventory-items.store') }}" class="card grid gap-4">
            @csrf
            <div>
                <x-input-label for="item_name" value="Item name" />
                <input id="item_name" name="item_name" value="{{ old('item_name') }}" class="form-control mt-1" required>
            </div>
            <div>
                <x-input-label for="category" value="Category" />
                <input id="category" name="category" value="{{ old('category') }}" class="form-control mt-1" placeholder="e.g. Furniture, IT, Books" required>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="quantity" value="Quantity" />
                    <input id="quantity" type="number" min="0" name="quantity" value="{{ old('quantity', 0) }}" class="form-control mt-1" required>
                </div>
                <div>
                    <x-input-label for="unit_price" value="Unit price ($)" />
                    <input id="unit_price" type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price') }}" class="form-control mt-1" placeholder="Price per unit">
                </div>
            </div>
            <div>
                <x-input-label for="condition" value="Condition" />
                <select id="condition" name="condition" class="form-control mt-1" required>
                    @foreach(\App\Models\InventoryItem::CONDITIONS as $value => $label)
                        <option value="{{ $value }}" @selected(old('condition', 'new') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="purchase_date" value="Purchase date" />
                    <input id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-control mt-1">
                </div>
                <div>
                    <x-input-label for="low_stock_threshold" value="Low stock alert (optional)" />
                    <input id="low_stock_threshold" type="number" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold') }}" class="form-control mt-1" placeholder="Default {{ \App\Models\InventoryItem::DEFAULT_LOW_STOCK_THRESHOLD }}">
                </div>
            </div>
            <div>
                <x-input-label for="notes" value="Notes / assignment" />
                <textarea id="notes" name="notes" rows="2" class="form-control mt-1" placeholder="Optional usage or location">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Save</button>
                <a href="{{ route('inventory-items.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
