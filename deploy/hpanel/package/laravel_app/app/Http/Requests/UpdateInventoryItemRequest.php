<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:64'],
            'quantity' => ['required', 'integer', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'condition' => ['required', 'string', Rule::in(array_keys(InventoryItem::CONDITIONS))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
