<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_ADDRESS = 'Laascaanood, Waqooyi Bari';

    /**
     * Normalize legacy / empty addresses to the correct school location.
     */
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        foreach (DB::table('system_settings')->orderBy('id')->get() as $row) {
            $addr = (string) ($row->address ?? '');
            $lower = strtolower($addr);
            $shouldUpdate = $addr === ''
                || str_contains($lower, 'hargeisa')
                || str_contains($lower, 'somaliland');

            if ($shouldUpdate) {
                DB::table('system_settings')->where('id', $row->id)->update([
                    'address' => self::NEW_ADDRESS,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally no-op: cannot safely restore previous free-text addresses.
    }
};
