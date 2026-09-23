<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'statistics' => [
                'users' => User::count(),
                'workers' => User::where('role', 'worker')->count(),
                'customers' => User::where('role', 'customer')->count(),
                'blocked_users' => User::where('is_blocked', true)->count(),
                'requests' => ServiceRequest::count(),
                'pending_requests' => ServiceRequest::where('status', 'pending')->count(),
                'accepted_requests' => ServiceRequest::where('status', 'accepted')->count(),
                'in_progress_requests' => ServiceRequest::where('status', 'in_progress')->count(),
                'completed_requests' => ServiceRequest::where('status', 'completed')->count(),
                'cancelled_requests' => ServiceRequest::where('status', 'cancelled')->count(),
                'reviews' => Review::count(),
                'categories' => Category::count(),
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['category', 'area'])
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

        return response()->json($users);
    }

    public function workers(): JsonResponse
    {
        $workers = User::where('role', 'worker')
            ->with(['category', 'area', 'verification'])
            ->latest()
            ->paginate(15);

        return response()->json($workers);
    }

    public function customers(): JsonResponse
    {
        $customers = User::where('role', 'customer')
            ->with('area')
            ->latest()
            ->paginate(15);

        return response()->json($customers);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'categories' => Category::withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function areas(): JsonResponse
    {
        return response()->json([
            'areas' => \App\Models\Area::withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function requests(): JsonResponse
    {
        $requests = ServiceRequest::with([
            'customer',
            'worker.category',
            'worker.area',
            'category',
            'area',
            'review',
        ])
            ->latest()
            ->paginate(15);

        return response()->json($requests);
    }

    public function reviews(): JsonResponse
    {
        $reviews = Review::with([
            'customer',
            'worker',
            'serviceRequest',
        ])
            ->latest()
            ->paginate(15);

        return response()->json($reviews);
    }

    public function blockUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'لا يمكن حظر Admin.',
            ], 422);
        }

        $user->update([
            'is_blocked' => true,
        ]);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'تم حظر المستخدم.',
        ]);
    }

    public function unblockUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $user->update([
            'is_blocked' => false,
        ]);

        return response()->json([
            'message' => 'تم إلغاء حظر المستخدم.',
        ]);
    }

    public function deleteUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'لا يمكن حذف Admin.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'تم حذف المستخدم.',
        ]);
    }

    public function statistics(): JsonResponse
    {
        $completed = ServiceRequest::where('status', 'completed')->count();

        $averageRating = Review::avg('rating');

        return response()->json([
            'statistics' => [
                'total_users' => User::count(),
                'total_workers' => User::where('role', 'worker')->count(),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_requests' => ServiceRequest::count(),
                'completed_requests' => $completed,
                'total_reviews' => Review::count(),
                'average_rating' => round((float) $averageRating, 1),
            ],
        ]);
    }

    public function reports(): JsonResponse
    {
        return response()->json([
            'requests_by_status' => ServiceRequest::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get(),

            'workers_by_category' => User::where('role', 'worker')
                ->selectRaw('category_id, COUNT(*) as total')
                ->groupBy('category_id')
                ->with('category')
                ->get(),

            'users_by_area' => User::selectRaw('area_id, COUNT(*) as total')
                ->whereNotNull('area_id')
                ->groupBy('area_id')
                ->with('area')
                ->get(),

            'reviews_by_rating' => Review::selectRaw('rating, COUNT(*) as total')
                ->groupBy('rating')
                ->orderBy('rating')
                ->get(),
        ]);
    }
}
