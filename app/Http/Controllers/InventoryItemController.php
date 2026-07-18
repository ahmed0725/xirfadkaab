<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $lowStockOnly = $request->boolean('low_stock');

        $query = InventoryItem::query()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($lowStockOnly, function ($q) {
                $q->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [InventoryItem::DEFAULT_LOW_STOCK_THRESHOLD]);
            })
            ->orderBy('item_name');

        $items = $query->paginate(20)->withQueryString();

        $categories = InventoryItem::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $filters = compact('category', 'lowStockOnly');

        return view('inventory.index', compact('items', 'filters', 'categories'));
    }

    public function create(): View
    {
        return view('inventory.create');
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        InventoryItem::create($request->validated());

        return redirect()->route('inventory-items.index')->with('success', 'Item added.');
    }

    public function show(InventoryItem $inventoryItem): View
    {
        return view('inventory.show', ['item' => $inventoryItem]);
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return view('inventory.edit', ['item' => $inventoryItem]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->update($request->validated());

        return redirect()->route('inventory-items.index')->with('success', 'Item updated.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->delete();

        return redirect()->route('inventory-items.index')->with('success', 'Item deleted.');
    }
}
