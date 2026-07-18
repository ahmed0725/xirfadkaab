<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-800">{{ $item->item_name }}</h2></x-slot>
    <div class="mx-auto max-w-2xl card space-y-2 text-sm">
        <p><span class="font-semibold">Category:</span> {{ $item->category }}</p>
        <p><span class="font-semibold">Quantity:</span> {{ $item->quantity }} @if($item->isLowStock())<span class="text-amber-700">(low stock)</span>@endif</p>
        <p><span class="font-semibold">Unit price:</span> {{ $item->unit_price !== null ? '$'.number_format((float) $item->unit_price, 2) : '—' }}</p>
        <p><span class="font-semibold">Total value:</span> {{ $item->totalValue() !== null ? '$'.number_format($item->totalValue(), 2) : '—' }}</p>
        <p><span class="font-semibold">Condition:</span> {{ \App\Models\InventoryItem::CONDITIONS[$item->condition] ?? $item->condition }}</p>
        <p><span class="font-semibold">Purchase date:</span> {{ $item->purchase_date?->format('Y-m-d') ?? '—' }}</p>
        <p><span class="font-semibold">Low stock threshold:</span> {{ $item->low_stock_threshold ?? \App\Models\InventoryItem::DEFAULT_LOW_STOCK_THRESHOLD }} (default if empty)</p>
        @if($item->notes)
            <p><span class="font-semibold">Notes:</span> {{ $item->notes }}</p>
        @endif
        <div class="flex gap-2 pt-4">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('inventory-items.edit', $item) }}" class="btn-primary">Edit</a>
            @endif
            <a href="{{ route('inventory-items.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>
</x-app-layout>
