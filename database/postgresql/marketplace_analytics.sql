-- Run against trrrible_analytics after creating the main Marketplace tables.
CREATE TABLE IF NOT EXISTS marketplace_product_metrics (
    product_id BIGINT PRIMARY KEY,
    product_title VARCHAR(160) NOT NULL,
    seller_id BIGINT NOT NULL,
    units_sold BIGINT NOT NULL DEFAULT 0,
    gross_sales NUMERIC(14,2) NOT NULL DEFAULT 0,
    refreshed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS marketplace_product_sales_idx
    ON marketplace_product_metrics (gross_sales DESC);
CREATE INDEX IF NOT EXISTS marketplace_seller_sales_idx
    ON marketplace_product_metrics (seller_id, gross_sales DESC);
CREATE OR REPLACE VIEW view_marketplace_analytics_overview AS
SELECT COUNT(*) AS products_tracked,
       COALESCE(SUM(units_sold), 0) AS units_sold,
       COALESCE(SUM(gross_sales), 0)::NUMERIC(14,2) AS gross_sales,
       COUNT(*) FILTER (WHERE units_sold > 0) AS products_with_sales
FROM marketplace_product_metrics;
