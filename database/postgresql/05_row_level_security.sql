-- ============================================================
-- TRRRIBLE MSBD: ROW LEVEL SECURITY
-- Apply manually after the main database has been initialized.
-- Run as PostgreSQL database owner (postgres).
-- ============================================================

BEGIN;

-- 1. User context helpers
CREATE OR REPLACE FUNCTION public.app_current_user_id()
RETURNS BIGINT
LANGUAGE SQL
STABLE
AS $$
    SELECT NULLIF(current_setting('app.user_id', true), '')::BIGINT;
$$;

CREATE OR REPLACE FUNCTION public.app_is_admin()
RETURNS BOOLEAN
LANGUAGE SQL
STABLE
AS $$
    SELECT COALESCE(current_setting('app.user_role', true) = 'admin', false);
$$;

-- 2. Secure ownership checks.
-- SECURITY DEFINER allows these helpers to inspect parent rows
-- without triggering recursive RLS policies.
CREATE OR REPLACE FUNCTION public.app_order_belongs_to_buyer(p_order_id BIGINT)
RETURNS BOOLEAN
LANGUAGE SQL
STABLE
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM public.orders o
        WHERE o.id = p_order_id
          AND o.buyer_id = public.app_current_user_id()
    );
$$;

CREATE OR REPLACE FUNCTION public.app_is_shot_owner(p_shot_id BIGINT)
RETURNS BOOLEAN
LANGUAGE SQL
STABLE
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM public.shots s
        WHERE s.id = p_shot_id
          AND s.user_id = public.app_current_user_id()
    );
$$;

-- 3. Seller revenue function.
-- Returns only the current seller's aggregate sales amount.
CREATE OR REPLACE FUNCTION public.fn_my_marketplace_sales()
RETURNS NUMERIC
LANGUAGE SQL
STABLE
SECURITY DEFINER
SET search_path = pg_catalog, public
AS $$
    SELECT COALESCE(SUM(oi.line_total), 0)::NUMERIC(14,2)
    FROM public.order_items oi
    JOIN public.orders o ON o.id = oi.order_id
    WHERE oi.seller_id = public.app_current_user_id()
      AND o.status = 'paid';
$$;

-- 4. Remove default PUBLIC execution from helper functions.
REVOKE ALL ON FUNCTION public.app_current_user_id() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.app_is_admin() FROM PUBLIC;
REVOKE ALL ON FUNCTION public.app_order_belongs_to_buyer(BIGINT) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.app_is_shot_owner(BIGINT) FROM PUBLIC;
REVOKE ALL ON FUNCTION public.fn_my_marketplace_sales() FROM PUBLIC;

GRANT EXECUTE ON FUNCTION public.app_current_user_id() TO trrrible_app;
GRANT EXECUTE ON FUNCTION public.app_is_admin() TO trrrible_app;
GRANT EXECUTE ON FUNCTION public.app_order_belongs_to_buyer(BIGINT) TO trrrible_app;
GRANT EXECUTE ON FUNCTION public.app_is_shot_owner(BIGINT) TO trrrible_app;
GRANT EXECUTE ON FUNCTION public.fn_my_marketplace_sales() TO trrrible_app;

-- 5. Enable RLS on private application tables.
ALTER TABLE public.carts ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.cart_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.order_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.project_inquiries ENABLE ROW LEVEL SECURITY;

-- 6. Carts: users can access only their own carts.
CREATE POLICY carts_owner_policy
ON public.carts
FOR ALL TO trrrible_app
USING (
    public.app_is_admin()
    OR user_id = public.app_current_user_id()
)
WITH CHECK (
    public.app_is_admin()
    OR user_id = public.app_current_user_id()
);

-- 7. Cart items: access follows ownership of the parent cart.
CREATE POLICY cart_items_owner_policy
ON public.cart_items
FOR ALL TO trrrible_app
USING (
    public.app_is_admin()
    OR EXISTS (
        SELECT 1
        FROM public.carts c
        WHERE c.id = cart_items.cart_id
          AND c.user_id = public.app_current_user_id()
    )
)
WITH CHECK (
    public.app_is_admin()
    OR EXISTS (
        SELECT 1
        FROM public.carts c
        WHERE c.id = cart_items.cart_id
          AND c.user_id = public.app_current_user_id()
    )
);

-- 8. Orders: buyers and administrators can read their orders.
CREATE POLICY orders_buyer_select_policy
ON public.orders
FOR SELECT TO trrrible_app
USING (
    public.app_is_admin()
    OR buyer_id = public.app_current_user_id()
);

CREATE POLICY orders_buyer_insert_policy
ON public.orders
FOR INSERT TO trrrible_app
WITH CHECK (
    public.app_is_admin()
    OR buyer_id = public.app_current_user_id()
);

-- 9. Order items: buyers and related sellers can read relevant items.
CREATE POLICY order_items_related_select_policy
ON public.order_items
FOR SELECT TO trrrible_app
USING (
    public.app_is_admin()
    OR seller_id = public.app_current_user_id()
    OR public.app_order_belongs_to_buyer(order_id)
);

CREATE POLICY order_items_buyer_insert_policy
ON public.order_items
FOR INSERT TO trrrible_app
WITH CHECK (
    public.app_is_admin()
    OR public.app_order_belongs_to_buyer(order_id)
);

-- 10. Payments: only the buyer of the order and administrators.
CREATE POLICY payments_buyer_select_policy
ON public.payments
FOR SELECT TO trrrible_app
USING (
    public.app_is_admin()
    OR public.app_order_belongs_to_buyer(order_id)
);

CREATE POLICY payments_buyer_insert_policy
ON public.payments
FOR INSERT TO trrrible_app
WITH CHECK (
    public.app_is_admin()
    OR public.app_order_belongs_to_buyer(order_id)
);

-- 11. Project inquiries: sender, design owner, or administrator.
CREATE POLICY project_inquiries_related_select_policy
ON public.project_inquiries
FOR SELECT TO trrrible_app
USING (
    public.app_is_admin()
    OR user_id = public.app_current_user_id()
    OR public.app_is_shot_owner(shots_id)
);

CREATE POLICY project_inquiries_sender_insert_policy
ON public.project_inquiries
FOR INSERT TO trrrible_app
WITH CHECK (
    public.app_is_admin()
    OR user_id = public.app_current_user_id()
);

COMMIT;
