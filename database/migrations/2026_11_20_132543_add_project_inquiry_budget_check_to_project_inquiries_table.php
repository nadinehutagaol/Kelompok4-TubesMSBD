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
                    WHERE conname = 'project_inquiries_budget_nonnegative'
                ) THEN
                    ALTER TABLE project_inquiries
                    ADD CONSTRAINT project_inquiries_budget_nonnegative
                    CHECK (project_budget IS NULL OR project_budget >= 0);
                END IF;
            END $$;
        ");
    }

    public function down(): void
    {
        // Constraint tidak dihapus karena penerapannya dikelola terpisah.
    }
};
