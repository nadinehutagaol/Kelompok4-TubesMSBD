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
                    WHERE conname = 'payments_status_valid'
                ) THEN
                    ALTER TABLE payments
                    ADD CONSTRAINT payments_status_valid
                    CHECK (status IN ('pending', 'paid', 'failed', 'refunded'));
                END IF;
            END $$;
        ");
    }

    public function down(): void
    {
        // Constraint tidak dihapus karena penerapannya dikelola terpisah.
    }
};
