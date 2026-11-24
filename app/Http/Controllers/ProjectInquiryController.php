<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectInquiry;
use App\Models\Shot;

class ProjectInquiryController extends Controller
{
    public function store(Request $request, $shotId)
    {
        $data = $request->validate([
            'message' => 'required|string|min:50',
            'target_date' => 'required|string',
            'budget' => 'nullable|numeric|min:0',
            'recommend_budget' => 'nullable',
            'contact_person' => 'required|string|max:255',
        ]);

        $shot = Shot::findOrFail($shotId);
        abort_if(
    (int) $shot->user_id === (int) $request->user()->id,
    403,
    'Kamu tidak dapat mengirim inquiry ke karya sendiri.'
);

        ProjectInquiry::create([
            'user_id' => $request->user()->id,
            'shots_id' => $shot->id,
            'project_details' => $data['message'],
            'target_date' => $data['target_date'],
            'project_budget' => $data['budget'] ?? null,
            'recommend_budget' => $request->has('recommend_budget'),
            'contact_person' => $data['contact_person'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan Project Inquiry berhasil dikirim!',
        ]);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $inquiries = ProjectInquiry::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('shot', function ($shotQuery) use ($userId) {
                    $shotQuery->where('user_id', $userId);
                });
        })
            ->with(['shot', 'user'])
            ->latest()
            ->get();

        return view('inquiries.index', compact('inquiries'));
    }
}
