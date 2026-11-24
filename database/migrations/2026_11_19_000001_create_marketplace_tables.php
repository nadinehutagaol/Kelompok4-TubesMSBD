<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        DB::table('product_categories')->insert([
            ['name' => 'UI Kits', 'slug' => 'ui-kits', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Templates', 'slug' => 'templates', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Icons & Illustrations', 'slug' => 'icons-illustrations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mockups', 'slug' => 'mockups', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fonts & Graphics', 'slug' => 'fonts-graphics', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->string('preview_path')->nullable();
            $table->string('file_path');
            $table->unsignedInteger('stock')->default(1000000);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('sales_count')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'category_id', 'created_at'], 'products_catalog_idx');
            $table->index(['seller_id', 'created_at'], 'products_seller_idx');
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('status', 24)->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
            $table->index(['buyer_id', 'created_at'], 'orders_buyer_idx');
            $table->index(['status', 'created_at'], 'orders_status_idx');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            $table->string('product_title', 160);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('method', 30)->default('simulation');
            $table->string('status', 24)->default('pending');
            $table->string('reference', 60)->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedSmallInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'user_id', 'order_id']);
        });

        Schema::create('marketplace_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('event_type', 40);
            $table->string('payment_status', 24)->nullable();
            $table->timestampTz('recorded_at')->useCurrent();
            $table->index(['event_type', 'recorded_at'], 'marketplace_audit_event_idx');
        });

        // PostgreSQL enforces domain rules even when data is inserted outside Laravel.
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_price_nonnegative CHECK (price >= 0)");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending','paid','processing','completed','cancelled','failed'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending','paid','failed','refunded'))");
        DB::statement("ALTER TABLE product_reviews ADD CONSTRAINT product_reviews_rating_check CHECK (rating BETWEEN 1 AND 5)");

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_log_marketplace_payment() RETURNS trigger AS $$
            BEGIN
                INSERT INTO marketplace_audit_logs (payment_id, event_type, payment_status, recorded_at)
                VALUES (NEW.id, CASE WHEN TG_OP = 'INSERT' THEN 'PAYMENT_CREATED' ELSE 'PAYMENT_STATUS_CHANGED' END, NEW.status, CURRENT_TIMESTAMP);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_marketplace_payment_audit
            AFTER INSERT OR UPDATE ON payments
            FOR EACH ROW EXECUTE FUNCTION fn_log_marketplace_payment();

            CREATE OR REPLACE VIEW view_marketplace_sales_summary AS
            SELECT p.id AS product_id, p.title, p.seller_id,
                   COALESCE(SUM(oi.quantity) FILTER (WHERE o.status = 'paid'), 0)::bigint AS units_sold,
                   COALESCE(SUM(oi.line_total) FILTER (WHERE o.status = 'paid'), 0)::numeric(14,2) AS gross_sales
            FROM products p
            LEFT JOIN order_items oi ON oi.product_id = p.id
            LEFT JOIN orders o ON o.id = oi.order_id
            GROUP BY p.id, p.title, p.seller_id;

            CREATE OR REPLACE FUNCTION fn_product_sales_total(p_product_id bigint)
            RETURNS numeric(14,2) AS $$
                SELECT COALESCE(SUM(oi.line_total), 0)::numeric(14,2)
                FROM order_items oi JOIN orders o ON o.id = oi.order_id
                WHERE oi.product_id = p_product_id AND o.status = 'paid';
            $$ LANGUAGE sql STABLE;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_marketplace_sales_summary');
        DB::statement('DROP FUNCTION IF EXISTS fn_product_sales_total(bigint)');
        DB::statement('DROP TRIGGER IF EXISTS trg_marketplace_payment_audit ON payments');
        DB::statement('DROP FUNCTION IF EXISTS fn_log_marketplace_payment()');
        Schema::dropIfExists('marketplace_audit_logs');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
