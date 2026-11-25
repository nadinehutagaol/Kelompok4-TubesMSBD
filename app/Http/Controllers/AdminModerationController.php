<?php

namespace App\Http\Controllers;

use App\Models\Shot;
use App\Models\Comment;
use App\Models\Marketplace\Product;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class AdminModerationController extends Controller
{
    // ==========================
    // SHOTS
    // ==========================

    public function shots()
    {
        $shots = Shot::with('user')
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(15);

        return view('admin.shots.index', compact('shots'));
    }

    public function showShot(Shot $shot)
    {
        $shot->load([
            'user',
            'categories',
            'comments.user'
        ]);

        $shot->loadCount(['likes', 'comments']);

        return view('admin.shots.show', compact('shot'));
    }

    public function destroyShot(Shot $shot)
    {
        DB::transaction(function () use ($shot) {
            $shot->delete();
        });

        return redirect()
            ->route('admin.shots.index')
            ->with('success', 'Shot deleted successfully.');
    }

    // ==========================
    // COMMENTS
    // ==========================

    public function destroyComment(Comment $comment)
    {
        $shotId = $comment->shot_id;

        $comment->delete();

        return redirect()
            ->route('admin.shots.show', $shotId)
            ->with('success', 'Comment deleted successfully.');
    }

    // ==========================
    // MARKETPLACE
    // ==========================

    public function products()
    {
        $products = Product::with(['seller', 'category'])
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function deactivateProduct(Product $product)
    {
        $product->update([
            'is_active' => false
        ]);

        return back()
            ->with('success', 'Product deactivated successfully.');
    }

    // ==========================
    // JOBS
    // ==========================

    public function jobs()
    {
        $jobs = Job::with('poster')
            ->latest()
            ->paginate(15);

        return view('admin.jobs.index', compact('jobs'));
    }

    public function destroyJob(Job $job)
    {
        $job->delete();

        return back()
            ->with('success', 'Job deleted successfully.');
    }
}
