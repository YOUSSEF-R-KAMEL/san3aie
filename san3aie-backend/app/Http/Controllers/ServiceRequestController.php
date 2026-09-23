<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\AppNotification;
class ServiceRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'message' => 'فقط العميل يمكنه إنشاء طلب خدمة.',
            ], 403);
        }

        $validated = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'description' => ['required', 'string', 'max:3000'],
            'location' => ['required', 'string', 'max:500'],
        ]);

        $worker = User::where('id', $validated['worker_id'])
            ->where('role', 'worker')
            ->first();

        if (!$worker) {
            return response()->json([
                'message' => 'الصنايعي غير موجود.',
            ], 404);
        }

        if ((int) $worker->category_id !== (int) $validated['category_id']) {
            return response()->json([
                'message' => 'المهنة المختارة لا تطابق مهنة الصنايعي.',
            ], 422);
        }

        $serviceRequest = ServiceRequest::create([
            'customer_id' => $user->id,
            'worker_id' => $worker->id,
            'category_id' => $validated['category_id'],
            'area_id' => $validated['area_id'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'status' => 'pending',
        ]);

        $worker->notify(new AppNotification(
            'new_request',
            'طلب خدمة جديد',
            'لديك طلب خدمة جديد من ' . $user->name,
            $serviceRequest->id
        ));

        $serviceRequest->load([
            'customer',
            'worker.category',
            'worker.area',
            'category',
            'area',
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب الخدمة بنجاح.',
            'request' => $serviceRequest,
        ], 201);
    }

    public function customerRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'customer') {
            return response()->json([
                'message' => 'هذه الصفحة مخصصة للعملاء.',
            ], 403);
        }

        $requests = ServiceRequest::query()
            ->where('customer_id', $user->id)
            ->with([
                'worker.category',
                'worker.area',
                'category',
                'area',
            ])
            ->latest()
            ->paginate(10);

        return response()->json($requests);
    }

    public function workerRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'هذه الصفحة مخصصة للصنايعية.',
            ], 403);
        }

        $requests = ServiceRequest::query()
            ->where('worker_id', $user->id)
            ->with([
                'customer',
                'category',
                'area',
            ])
            ->latest()
            ->paginate(10);

        return response()->json($requests);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $serviceRequest = ServiceRequest::query()
            ->with([
                'customer',
                'worker.category',
                'worker.area',
                'category',
                'area',
            ])
            ->findOrFail($id);

        if (
            $serviceRequest->customer_id !== $user->id &&
            $serviceRequest->worker_id !== $user->id
        ) {
            return response()->json([
                'message' => 'غير مصرح لك بمشاهدة هذا الطلب.',
            ], 403);
        }

        return response()->json([
            'request' => $serviceRequest,
        ]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'فقط الصنايعي يمكنه قبول الطلب.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::where('id', $id)
            ->where('worker_id', $user->id)
            ->firstOrFail();

        if ($serviceRequest->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن قبول هذا الطلب في حالته الحالية.',
            ], 422);
        }

        $serviceRequest->update([
            'status' => 'accepted',
        ]);

        $serviceRequest->customer->notify(new AppNotification(
            'request_accepted',
            'تم قبول طلبك',
            'قام الصنايعي ' . $user->name . ' بقبول طلب الخدمة.',
            $serviceRequest->id
        ));

        $serviceRequest->load([
            'customer',
            'worker.category',
            'worker.area',
            'category',
            'area',
        ]);

        return response()->json([
            'message' => 'تم قبول الطلب.',
            'request' => $serviceRequest,
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'فقط الصنايعي يمكنه رفض الطلب.',
            ], 403);
        }

        $serviceRequest = ServiceRequest::where('id', $id)
            ->where('worker_id', $user->id)
            ->firstOrFail();

        if ($serviceRequest->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن رفض هذا الطلب في حالته الحالية.',
            ], 422);
        }

        $serviceRequest->update([
            'status' => 'rejected',
        ]);

        $serviceRequest->customer->notify(new AppNotification(
            'request_rejected',
            'تم رفض طلبك',
            'قام الصنايعي ' . $user->name . ' برفض طلب الخدمة.',
            $serviceRequest->id
        ));

        return response()->json([
            'message' => 'تم رفض الطلب.',
            'request' => $serviceRequest,
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'in_progress',
                    'completed',
                ]),
            ],
        ]);

        $serviceRequest = ServiceRequest::where('id', $id)
            ->where('worker_id', $user->id)
            ->firstOrFail();

        $allowedStatuses = [
            'accepted' => ['in_progress'],
            'in_progress' => ['completed'],
        ];

        if (
            !isset($allowedStatuses[$serviceRequest->status]) ||
            !in_array($validated['status'], $allowedStatuses[$serviceRequest->status], true)
        ) {
            return response()->json([
                'message' => 'لا يمكن تغيير حالة الطلب بهذه الطريقة.',
            ], 422);
        }

        $serviceRequest->update([
            'status' => $validated['status'],
        ]);

        if ($validated['status'] === 'completed') {
            $serviceRequest->customer->notify(new AppNotification(
                'request_completed',
                'تم إكمال الخدمة',
                'تم الانتهاء من طلب الخدمة ويمكنك الآن تقييم الصنايعي.',
                $serviceRequest->id
            ));
        }

        return response()->json([
            'message' => 'تم تحديث حالة الطلب.',
            'request' => $serviceRequest,
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $serviceRequest = ServiceRequest::where('id', $id)
            ->where('customer_id', $user->id)
            ->firstOrFail();

        if (!in_array($serviceRequest->status, ['pending', 'accepted'], true)) {
            return response()->json([
                'message' => 'لا يمكن إلغاء الطلب في حالته الحالية.',
            ], 422);
        }

        $serviceRequest->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'تم إلغاء الطلب.',
            'request' => $serviceRequest,
        ]);
    }
}
