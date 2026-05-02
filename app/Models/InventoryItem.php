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
        'purchase_date',
        'condition',
        'notes',
        'low_stock_threshold',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function effectiveLowStockThreshold(): int
    {
        return $this->low_stock_threshold ?? self::DEFAULT_LOW_STOCK_THRESHOLD;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->effectiveLowStockThreshold();
    }
}
