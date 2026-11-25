<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefreshAnalytics extends Command
{
    protected $signature = 'analytics:refresh';
    protected $description = 'Refresh reporting aggregates in the separate PostgreSQL analytics database';

    public function handle(): int
    {
        try {
            $designers = DB::connection('pgsql')->table('view_top_designers')
                ->orderByDesc('total_likes')
                ->get(['id', 'full_name', 'username', 'total_karya', 'total_likes']);

            $marketplace = DB::connection('pgsql')->table('view_marketplace_sales_summary')->get(['product_id', 'title', 'seller_id', 'units_sold', 'gross_sales']);

            $daily = DB::connection('pgsql')->select(<<<'SQL'
                WITH activity AS (
                    SELECT DATE(created_at) AS metric_date, 'shot'::text AS activity_type, user_id FROM shots WHERE created_at IS NOT NULL
                    UNION ALL
                    SELECT DATE(created_at), 'like'::text, user_id FROM likes
                    UNION ALL
                    SELECT DATE(created_at), 'comment'::text, user_id FROM comments WHERE created_at IS NOT NULL
                )
                SELECT metric_date,
                       COUNT(*) FILTER (WHERE activity_type = 'shot')::bigint AS total_shots,
                       COUNT(*) FILTER (WHERE activity_type = 'like')::bigint AS total_likes,
                       COUNT(*) FILTER (WHERE activity_type = 'comment')::bigint AS total_comments,
                       COUNT(DISTINCT user_id)::bigint AS active_users
                FROM activity
                GROUP BY metric_date
                ORDER BY metric_date
            SQL);

            DB::connection('analytics')->transaction(function () use ($designers, $daily, $marketplace) {
                DB::connection('analytics')->table('designer_metrics')->delete();
                DB::connection('analytics')->table('daily_metrics')->delete();
                DB::connection('analytics')->table('marketplace_product_metrics')->delete();

                foreach ($designers->chunk(500) as $chunk) {
                    DB::connection('analytics')->table('designer_metrics')->insert(
                        $chunk->map(fn ($row) => [
                            'designer_id' => $row->id,
                            'full_name' => $row->full_name,
                            'username' => $row->username,
                            'total_karya' => $row->total_karya,
                            'total_likes' => $row->total_likes,
                            'refreshed_at' => now(),
                        ])->all()
                    );
                }

                foreach (array_chunk(array_map(fn ($row) => [
                    'metric_date' => $row->metric_date,
                    'total_shots' => $row->total_shots,
                    'total_likes' => $row->total_likes,
                    'total_comments' => $row->total_comments,
                    'active_users' => $row->active_users,
                    'refreshed_at' => now(),
                ], $daily), 500) as $chunk) {
                    DB::connection('analytics')->table('daily_metrics')->insert($chunk);
                }

                foreach ($marketplace->chunk(500) as $chunk) {
                    DB::connection('analytics')->table('marketplace_product_metrics')->insert(
                        $chunk->map(fn ($row) => [
                            'product_id' => $row->product_id,
                            'product_title' => $row->title,
                            'seller_id' => $row->seller_id,
                            'units_sold' => $row->units_sold,
                            'gross_sales' => $row->gross_sales,
                            'refreshed_at' => now(),
                        ])->all()
                    );
                }

                DB::connection('analytics')->table('analytics_refresh_logs')->insert([
                    'refreshed_at' => now(),
                    'designer_rows' => $designers->count(),
                    'daily_rows' => count($daily),
                    'status' => 'success',
                ]);
            });

            $this->info('Analytics refreshed: '.$designers->count().' designers, '.count($daily).' daily rows, '.$marketplace->count().' marketplace products.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Analytics refresh failed: '.$exception->getMessage());
            return self::FAILURE;
        }
    }
}
