<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $distance = $request->distance;

        $query = User::with(['category', 'area'])
            ->where('role', 'worker')
            ->where('is_blocked', false)
            ->withAvg('reviewsReceived', 'rating')
            ->withCount([
                'workerRequests as completed_jobs_count' => function ($query) {
                    $query->where('status', 'completed');
                },
            ]);

        $query
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('area_id'), function ($query) use ($request) {
                $query->where('area_id', $request->area_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('rating'), function ($query) use ($request) {
                $query->having(
                    'reviews_received_avg_rating',
                    '>=',
                    $request->rating
                );
            })
            ->when($request->has('is_available'), function ($query) use ($request) {
                $query->where(
                    'is_available',
                    filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN)
                );
            })
            ->when($request->has('is_verified'), function ($query) use ($request) {
                $query->where(
                    'is_verified',
                    filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN)
                );
            });

        if (
            $request->filled('latitude') &&
            $request->filled('longitude')
        ) {
            $query->select('users.*');

            $query->selectRaw(
                '(
                    6371 * acos(
                        cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))
                    )
                ) AS distance',
                [
                    $latitude,
                    $longitude,
                    $latitude,
                ]
            );

            if ($request->filled('distance')) {
                $query->having('distance', '<=', $distance);
            }
        }

        $workers = $query->get();

        $workers = $workers->map(function ($worker) {
            $subscription = $worker->subscriptions()
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->latest('expires_at')
                ->first();

            $featured = $worker->features()
                ->where('feature', 'featured')
                ->where('expires_at', '>', now())
                ->latest('expires_at')
                ->first();

            $worker->is_premium = (bool) $subscription;
            $worker->is_featured = (bool) $featured;
            $worker->premium_until = $subscription?->expires_at;
            $worker->featured_until = $featured?->expires_at;
            $worker->rating = round(
                (float) ($worker->reviews_received_avg_rating ?? 0),
                1
            );
            $worker->completed_jobs = (int) $worker->completed_jobs_count;

            if (isset($worker->distance)) {
                $worker->distance = round((float) $worker->distance, 2);
            }

            return $worker;
        });

        if ($request->has('premium')) {
            $premium = filter_var(
                $request->premium,
                FILTER_VALIDATE_BOOLEAN
            );

            $workers = $workers->filter(function ($worker) use ($premium) {
                return $worker->is_premium === $premium;
            });
        }

        if ($request->has('featured')) {
            $featured = filter_var(
                $request->featured,
                FILTER_VALIDATE_BOOLEAN
            );

            $workers = $workers->filter(function ($worker) use ($featured) {
                return $worker->is_featured === $featured;
            });
        }

        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'rating':
                $workers = $workers
                    ->sortByDesc('rating')
                    ->values();
                break;

            case 'distance':
                if (
                    $request->filled('latitude') &&
                    $request->filled('longitude')
                ) {
                    $workers = $workers
                        ->sortBy('distance')
                        ->values();
                }
                break;

            case 'completed_jobs':
                $workers = $workers
                    ->sortByDesc('completed_jobs')
                    ->values();
                break;

            case 'newest':
            default:
                $workers = $workers
                    ->sortByDesc('created_at')
                    ->values();
                break;
        }

        $workers = $workers
            ->sortByDesc(function ($worker) {
                if ($worker->is_featured) {
                    return 3;
                }

                if ($worker->is_premium) {
                    return 2;
                }

                return 1;
            })
            ->values();

        return response()->json([
            'workers' => $workers,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $worker = User::with(['category', 'area'])
            ->where('role', 'worker')
            ->where('is_blocked', false)
            ->withAvg('reviewsReceived', 'rating')
            ->withCount([
                'workerRequests as completed_jobs_count' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->findOrFail($id);

        $subscription = $worker->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $featured = $worker->features()
            ->where('feature', 'featured')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $worker->is_premium = (bool) $subscription;
        $worker->is_featured = (bool) $featured;
        $worker->premium_until = $subscription?->expires_at;
        $worker->featured_until = $featured?->expires_at;
        $worker->rating = round(
            (float) ($worker->reviews_received_avg_rating ?? 0),
            1
        );
        $worker->completed_jobs = (int) $worker->completed_jobs_count;

        return response()->json($worker);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'هذا الحساب ليس حساب صنايعي.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:70',
            ],
            'is_available' => ['required', 'boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store(
                'workers',
                'public'
            );
        }

        $user->update($validated);

        $user->load(['category', 'area']);

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح.',
            'worker' => $user,
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'هذا الحساب ليس حساب صنايعي.',
            ], 403);
        }

        $user->update([
            'is_available' => $validated['is_available'],
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة التوافر.',
            'is_available' => $user->is_available,
        ]);
    }
}
