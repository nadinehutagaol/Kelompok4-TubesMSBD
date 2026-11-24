SELECT setval(pg_get_serial_sequence('public.categories', 'id'), COALESCE((SELECT MAX(id) FROM categories), 1), EXISTS (SELECT 1 FROM categories));
SELECT setval(pg_get_serial_sequence('public.collections', 'id'), COALESCE((SELECT MAX(id) FROM collections), 1), EXISTS (SELECT 1 FROM collections));
SELECT setval(pg_get_serial_sequence('public.comments', 'id'), COALESCE((SELECT MAX(id) FROM comments), 1), EXISTS (SELECT 1 FROM comments));
SELECT setval(pg_get_serial_sequence('public.jobs', 'id'), COALESCE((SELECT MAX(id) FROM jobs), 1), EXISTS (SELECT 1 FROM jobs));
SELECT setval(pg_get_serial_sequence('public.migrations', 'id'), COALESCE((SELECT MAX(id) FROM migrations), 1), EXISTS (SELECT 1 FROM migrations));
SELECT setval(pg_get_serial_sequence('public.project_inquiries', 'id'), COALESCE((SELECT MAX(id) FROM project_inquiries), 1), EXISTS (SELECT 1 FROM project_inquiries));
SELECT setval(pg_get_serial_sequence('public.shots', 'id'), COALESCE((SELECT MAX(id) FROM shots), 1), EXISTS (SELECT 1 FROM shots));
SELECT setval(pg_get_serial_sequence('public.users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1), EXISTS (SELECT 1 FROM users));
