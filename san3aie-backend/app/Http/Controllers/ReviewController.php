<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Notifications\AppNotification;

class ReviewController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'message' => 'فقط العميل يمكنه إضافة تقييم.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::findOrFail($id);

        if ($serviceRequest->customer_id !== $user->id) {
            return response()->json([
                'message' => 'غير مصرح لك بتقييم هذا الطلب.',
            ], 403);
        }

        if ($serviceRequest->status !== 'completed') {
            return response()->json([
                'message' => 'يمكن تقييم الطلب بعد اكتمال الخدمة.',
            ], 422);
        }

        if ($serviceRequest->review()->exists()) {
            return response()->json([
                'message' => 'تم تقييم هذا الطلب من قبل.',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([
            'service_request_id' => $serviceRequest->id,
            'customer_id' => $user->id,
            'worker_id' => $serviceRequest->worker_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $serviceRequest->worker->notify(new AppNotification(
            'new_review',
            'تقييم جديد',
            'قام ' . $user->name . ' بإضافة تقييم جديد لك.',
            $serviceRequest->id
        ));

        $review->load(['customer', 'worker']);

        return response()->json([
            'message' => 'تم إضافة التقييم بنجاح.',
            'review' => $review,
        ], 201);
    }

    public function workerReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'هذه الصفحة مخصصة للصنايعي.',
            ], 403);
        }

        $reviews = Review::where('worker_id', $user->id)
            ->with('customer')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }
}
