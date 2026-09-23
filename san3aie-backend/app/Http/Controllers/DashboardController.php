<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'customer') {
            $totalRequests = ServiceRequest::where('customer_id', $user->id)->count();

            $pendingRequests = ServiceRequest::where('customer_id', $user->id)
                ->where('status', 'pending')
                ->count();

            $acceptedRequests = ServiceRequest::where('customer_id', $user->id)
                ->where('status', 'accepted')
                ->count();

            $completedRequests = ServiceRequest::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->count();

            $cancelledRequests = ServiceRequest::where('customer_id', $user->id)
                ->where('status', 'cancelled')
                ->count();

            return response()->json([
                'user' => $user->load(['area']),
                'statistics' => [
                    'total_requests' => $totalRequests,
                    'pending_requests' => $pendingRequests,
                    'accepted_requests' => $acceptedRequests,
                    'completed_requests' => $completedRequests,
                    'cancelled_requests' => $cancelledRequests,
                ],
            ]);
        }

        $totalServices = ServiceRequest::where('worker_id', $user->id)->count();

        $pendingRequests = ServiceRequest::where('worker_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $acceptedRequests = ServiceRequest::where('worker_id', $user->id)
            ->where('status', 'accepted')
            ->count();

        $inProgressRequests = ServiceRequest::where('worker_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $completedRequests = ServiceRequest::where('worker_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $averageRating = Review::where('worker_id', $user->id)
            ->avg('rating');

        $numberOfReviews = Review::where('worker_id', $user->id)->count();

        return response()->json([
            'user' => $user->load(['category', 'area']),
            'statistics' => [
                'total_services' => $totalServices,
                'pending_requests' => $pendingRequests,
                'accepted_requests' => $acceptedRequests,
                'in_progress_requests' => $inProgressRequests,
                'completed_requests' => $completedRequests,
                'average_rating' => round((float) $averageRating, 1),
                'number_of_reviews' => $numberOfReviews,
                'number_of_completed_jobs' => $completedRequests,
            ],
        ]);
    }
}
