<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category', 64);
            $table->unsignedInteger('quantity')->default(0);
            $table->date('purchase_date')->nullable();
            $table->string('condition', 16);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('low_stock_threshold')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
