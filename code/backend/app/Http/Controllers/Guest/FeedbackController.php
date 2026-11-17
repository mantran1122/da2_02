<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * Khách gửi đánh giá bữa ăn
     */
    public function store(Request $request): JsonResponse
    {
        $session = $request->get('current_session');

        $validated = $request->validate([
            'rating_food' => 'required|integer|min:1|max:5',
            'rating_service' => 'required|integer|min:1|max:5',
            'rating_overall' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $feedback = Feedback::create([
            'session_id' => $session->id,
            'rating_food' => $validated['rating_food'],
            'rating_service' => $validated['rating_service'],
            'rating_overall' => $validated['rating_overall'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn quý khách đã gửi đánh giá phản hồi!',
            'data' => $feedback
        ], 201);
    }
}
