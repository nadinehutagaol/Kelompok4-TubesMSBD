<?php

namespace App\Http\Controllers;

use App\Models\Analytics\DesignerMetric;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // The reporting data lives in the second PostgreSQL database.
        $topDesigners = DesignerMetric::query()
            ->orderByDesc('total_likes')
            ->orderByDesc('total_karya')
            ->paginate(10);

        // Preserve the existing report page before the first analytics refresh.
        if ($topDesigners->total() === 0) {
            $topDesigners = DB::connection('pgsql')->table('view_top_designers')
                ->orderByDesc('total_likes')
                ->orderByDesc('total_karya')
                ->paginate(10);
        }

        return view('admin.reports.index', compact('topDesigners'));
    }
}
