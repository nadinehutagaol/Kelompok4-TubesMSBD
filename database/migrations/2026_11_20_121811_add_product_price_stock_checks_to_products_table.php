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
                WHERE conname = 'products_price_nonnegative'
            ) THEN
                ALTER TABLE products
                ADD CONSTRAINT products_price_nonnegative
                CHECK (price >= 0);
            END IF;

            IF NOT EXISTS (
                SELECT 1 FROM pg_constraint
                WHERE conname = 'products_stock_nonnegative'
            ) THEN
                ALTER TABLE products
                ADD CONSTRAINT products_stock_nonnegative
                CHECK (stock >= 0);
            END IF;
        END $$;
    ");
}

    public function down(): void
{
    DB::statement("
        ALTER TABLE products
        DROP CONSTRAINT IF EXISTS products_stock_nonnegative
    ");
}
};