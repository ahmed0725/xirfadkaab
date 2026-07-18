<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">Inventory</h2></x-slot>
    <div class="space-y-4">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('inventory-items.create') }}" class="btn-primary">Add item</a>
        @endif

        <form method="GET" class="card flex flex-wrap items-end gap-3">
            <div>
                <x-input-label for="category" value="Category" />
                <select id="category" name="category" class="form-control mt-1">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="low_stock" value="1" @checked($filters['lowStockOnly'] ?? false)>
                Low stock only
            </label>
            <button type="submit" class="btn-primary">Apply</button>
        </form>

        <div class="table-shell overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-600">
                        <th class="p-3">Item</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Qty</th>
                        <th class="p-3">Unit price</th>
                        <th class="p-3">Total value</th>
                        <th class="p-3">Condition</th>
                        <th class="p-3">Purchase</th>
                        <th class="p-3">Notes</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-medium">{{ $item->item_name }}</td>
                            <td class="p-3">{{ $item->category }}</td>
                            <td class="p-3">
                                {{ $item->quantity }}
                                @if($item->isLowStock())
                                    <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-900">Low</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $item->unit_price !== null ? '$'.number_format((float) $item->unit_price, 2) : '—' }}</td>
                            <td class="p-3">{{ $item->totalValue() !== null ? '$'.number_format($item->totalValue(), 2) : '—' }}</td>
                            <td class="p-3">{{ \App\Models\InventoryItem::CONDITIONS[$item->condition] ?? $item->condition }}</td>
                            <td class="p-3">{{ $item->purchase_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="p-3 max-w-xs truncate">{{ $item->notes ?? '—' }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <a href="{{ route('inventory-items.show', $item) }}" class="text-blue-600">View</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('inventory-items.edit', $item) }}" class="ml-2 text-indigo-600">Edit</a>
                                    <form method="POST" action="{{ route('inventory-items.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-rose-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $items->links() }}</div>
    </div>
</x-app-layout>
