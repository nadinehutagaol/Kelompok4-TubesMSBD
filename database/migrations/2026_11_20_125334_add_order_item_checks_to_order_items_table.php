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
                    WHERE conname = 'order_items_unit_price_nonnegative'
                ) THEN
                    ALTER TABLE order_items
                    ADD CONSTRAINT order_items_unit_price_nonnegative
                    CHECK (unit_price >= 0);
                END IF;

                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'order_items_quantity_positive'
                ) THEN
                    ALTER TABLE order_items
                    ADD CONSTRAINT order_items_quantity_positive
                    CHECK (quantity > 0);
                END IF;

                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'order_items_line_total_nonnegative'
                ) THEN
                    ALTER TABLE order_items
                    ADD CONSTRAINT order_items_line_total_nonnegative
                    CHECK (line_total >= 0);
                END IF;
            END $$;
        ");
    }

    public function down(): void
    {
        // Constraint tidak dihapus karena penerapannya dikelola terpisah.
    }
};