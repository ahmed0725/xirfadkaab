<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'item_name' => fake()->words(2, true),
            'category' => fake()->randomElement(['furniture', 'it', 'books', 'supplies']),
            'quantity' => fake()->numberBetween(0, 50),
            'purchase_date' => fake()->optional()->date(),
            'condition' => fake()->randomElement(array_keys(InventoryItem::CONDITIONS)),
            'notes' => null,
            'low_stock_threshold' => null,
        ];
    }
}
