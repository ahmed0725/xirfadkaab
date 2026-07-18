<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    public const DEFAULT_LOW_STOCK_THRESHOLD = 5;

    public const CONDITIONS = [
        'new' => 'New',
        'used' => 'Used',
    ];

    protected $fillable = [
        'item_name',
        'category',
        'quantity',
        'unit_price',
        'purchase_date',
        'condition',
        'notes',
        'low_stock_threshold',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Stock value (quantity × unit price); null when no price is recorded.
     */
    public function totalValue(): ?float
    {
        if ($this->unit_price === null) {
            return null;
        }

        return $this->quantity * (float) $this->unit_price;
    }

    public function effectiveLowStockThreshold(): int
    {
        return $this->low_stock_threshold ?? self::DEFAULT_LOW_STOCK_THRESHOLD;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->effectiveLowStockThreshold();
    }
}
