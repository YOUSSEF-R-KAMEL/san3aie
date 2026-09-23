<?php

namespace App\Http\Controllers;

use App\Models\WorkerVerification;
use App\Notifications\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkerVerificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'فقط الصنايعي يمكنه طلب التحقق.',
            ], 403);
        }

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ]);

        $verification = WorkerVerification::where('worker_id', $user->id)->first();

        if ($verification && $verification->status === 'pending') {
            return response()->json([
                'message' => 'لديك طلب تحقق قيد المراجعة.',
            ], 422);
        }

        if ($verification && $verification->document_path) {
            Storage::disk('public')->delete($verification->document_path);
        }

        $path = $request->file('document')->store(
            'worker-verifications',
            'public'
        );

        $verification = WorkerVerification::updateOrCreate(
            [
                'worker_id' => $user->id,
            ],
            [
                'document_type' => $validated['document_type'],
                'document_path' => $path,
                'status' => 'pending',
                'admin_note' => null,
                'reviewed_at' => null,
            ]
        );

        $user->update([
            'is_verified' => false,
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب التحقق بنجاح.',
            'verification' => $verification,
        ], 201);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'worker') {
            return response()->json([
                'message' => 'غير مصرح لك.',
            ], 403);
        }

        $verification = WorkerVerification::where(
            'worker_id',
            $user->id
        )->first();

        return response()->json([
            'verification' => $verification,
            'is_verified' => $user->is_verified,
        ]);
    }

    public function index(): JsonResponse
    {
        $verifications = WorkerVerification::with([
            'worker.category',
            'worker.area',
        ])
            ->latest()
            ->paginate(15);

        return response()->json($verifications);
    }

    public function approve(int $id): JsonResponse
    {
        $verification = WorkerVerification::with('worker')
            ->findOrFail($id);

        $verification->update([
            'status' => 'approved',
            'admin_note' => null,
            'reviewed_at' => now(),
        ]);

        $verification->worker->update([
            'is_verified' => true,
        ]);

        $verification->worker->notify(new AppNotification(
            'verification_approved',
            'تم توثيق حسابك',
            'تمت الموافقة على طلب توثيق حسابك.',
        ));

        return response()->json([
            'message' => 'تم قبول طلب التحقق.',
            'verification' => $verification,
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $verification = WorkerVerification::with('worker')
            ->findOrFail($id);

        $verification->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'reviewed_at' => now(),
        ]);

        $verification->worker->update([
            'is_verified' => false,
        ]);

        $verification->worker->notify(new AppNotification(
            'verification_rejected',
            'تم رفض طلب التوثيق',
            $validated['admin_note'],
        ));

        return response()->json([
            'message' => 'تم رفض طلب التحقق.',
            'verification' => $verification,
        ]);
    }
}
