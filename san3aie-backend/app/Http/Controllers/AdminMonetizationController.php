<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkerFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMonetizationController extends Controller
{
    public function plans()
    {
        return response()->json(
            SubscriptionPlan::withCount('subscriptions')
                ->latest()
                ->get()
        );
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $plan = SubscriptionPlan::create($validated);

        return response()->json($plan, 201);
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'duration_days' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $plan->update($validated);

        return response()->json($plan->fresh());
    }

    public function deletePlan(int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        if ($plan->subscriptions()->exists()) {
            return response()->json([
                'message' => 'لا يمكن حذف خطة مرتبطة باشتراكات. يمكنك إيقافها بدلاً من ذلك.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'تم حذف الخطة.',
        ]);
    }

    public function subscriptions(Request $request)
    {
        $query = Subscription::with([
            'worker:id,name,phone',
            'plan:id,name,price,duration_days',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        return response()->json(
            $query->latest()->paginate(20)
        );
    }

    public function payments(Request $request)
    {
        $query = Payment::with([
            'worker:id,name,phone',
            'subscription.plan:id,name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return response()->json(
            $query->latest()->paginate(20)
        );
    }

    public function featuredWorkers()
    {
        return response()->json(
            WorkerFeature::with([
                'worker:id,name,phone,is_verified',
            ])
                ->where('feature', 'featured')
                ->latest()
                ->paginate(20)
        );
    }

    public function featureWorker(Request $request, int $workerId)
    {
        $validated = $request->validate([
            'duration_days' => ['required', 'integer', 'min:1'],
        ]);

        $worker = User::where('id', $workerId)
            ->where('role', 'worker')
            ->firstOrFail();

        $feature = WorkerFeature::create([
            'worker_id' => $worker->id,
            'feature' => 'featured',
            'starts_at' => now(),
            'expires_at' => now()->addDays($validated['duration_days']),
        ]);

        return response()->json([
            'message' => 'تم تفعيل Featured للصنايعي.',
            'feature' => $feature,
        ], 201);
    }

    public function removeFeature(int $workerId)
    {
        WorkerFeature::where('worker_id', $workerId)
            ->where('feature', 'featured')
            ->where('expires_at', '>', now())
            ->update([
                'expires_at' => now(),
            ]);

        return response()->json([
            'message' => 'تم إيقاف Featured.',
        ]);
    }

    public function statistics()
    {
        $totalPayments = Payment::count();
        $paidPayments = Payment::where('status', 'paid')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $failedPayments = Payment::where('status', 'failed')->count();
        $refundedPayments = Payment::where('status', 'refunded')->count();

        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $expiredSubscriptions = Subscription::where('status', 'expired')->count();

        $featuredWorkers = WorkerFeature::where('feature', 'featured')
            ->where('expires_at', '>', now())
            ->distinct('worker_id')
            ->count('worker_id');

        return response()->json([
            'total_payments' => $totalPayments,
            'paid_payments' => $paidPayments,
            'pending_payments' => $pendingPayments,
            'failed_payments' => $failedPayments,
            'refunded_payments' => $refundedPayments,
            'total_revenue' => $totalRevenue,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'featured_workers' => $featuredWorkers,
        ]);
    }

    public function refundPayment(int $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'paid') {
            return response()->json([
                'message' => 'لا يمكن عمل Refund لهذه العملية.',
            ], 422);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'refunded',
            ]);

            if ($payment->subscription) {
                $payment->subscription->update([
                    'status' => 'cancelled',
                ]);
            }
        });

        return response()->json([
            'message' => 'تم تسجيل Refund.',
            'payment' => $payment->fresh(),
        ]);
    }
}
