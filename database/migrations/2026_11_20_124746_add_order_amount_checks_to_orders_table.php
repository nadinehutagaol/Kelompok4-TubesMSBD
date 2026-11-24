<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'orders_subtotal_nonnegative'
                ) THEN
                    ALTER TABLE orders
                    ADD CONSTRAINT orders_subtotal_nonnegative
                    CHECK (subtotal >= 0);
                END IF;

                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'orders_total_nonnegative'
                ) THEN
                    ALTER TABLE orders
                    ADD CONSTRAINT orders_total_nonnegative
                    CHECK (total >= 0);
                END IF;
            END $$;
        ");
    }

    public function down(): void
{
    // Tidak menghapus constraint yang sudah diterapkan.
}
};