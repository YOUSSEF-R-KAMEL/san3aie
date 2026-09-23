<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\WorkerFeature;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return response()->json(
            SubscriptionPlan::where('is_active', true)
                ->orderBy('price')
                ->get()
        );
    }

    public function current(Request $request)
    {
        $worker = $request->user();

        if ($worker->role !== 'worker') {
            return response()->json([
                'message' => 'هذا القسم متاح للصنايعي فقط.',
            ], 403);
        }

        $subscription = Subscription::with('plan')
            ->where('worker_id', $worker->id)
            ->latest()
            ->first();

        if (
            $subscription &&
            $subscription->status === 'active' &&
            $subscription->expires_at?->isPast()
        ) {
            $subscription->update([
                'status' => 'expired',
            ]);

            $subscription->refresh();
        }

        $featured = WorkerFeature::where('worker_id', $worker->id)
            ->where('feature', 'featured')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return response()->json([
            'subscription' => $subscription,
            'is_premium' => $subscription?->status === 'active',
            'featured_until' => $featured?->expires_at,
            'is_featured' => (bool) $featured,
        ]);
    }

    public function subscribe(
        Request $request,
        PaymentService $paymentService
    ) {
        $worker = $request->user();

        if ($worker->role !== 'worker') {
            return response()->json([
                'message' => 'هذا القسم متاح للصنايعي فقط.',
            ], 403);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $plan = SubscriptionPlan::where('id', $validated['plan_id'])
            ->where('is_active', true)
            ->first();

        if (!$plan) {
            return response()->json([
                'message' => 'الخطة غير متاحة.',
            ], 422);
        }

        $activeSubscription = Subscription::where('worker_id', $worker->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeSubscription) {
            return response()->json([
                'message' => 'لديك اشتراك Premium فعال بالفعل.',
                'subscription' => $activeSubscription->load('plan'),
            ], 422);
        }

        $result = DB::transaction(function () use ($worker, $plan, $paymentService) {
            $subscription = Subscription::create([
                'worker_id' => $worker->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
            ]);

            $payment = Payment::create([
                'worker_id' => $worker->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'payment_method' => 'gateway',
                'status' => 'pending',
            ]);

            $gateway = $paymentService->createPayment($payment);

            return [
                'subscription' => $subscription->load('plan'),
                'payment' => $payment->fresh(),
                'gateway' => $gateway,
            ];
        });

        return response()->json($result, 201);
    }

    public function mockPayment(string $transactionId)
    {
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'عملية الدفع غير موجودة.',
            ], 404);
        }

        DB::transaction(function () use ($payment) {
            $subscription = $payment->subscription;
            $plan = $subscription->plan;

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
            ]);
        });

        return response()->json([
            'message' => 'تم الدفع بنجاح.',
            'payment' => $payment->fresh(),
            'subscription' => $payment->subscription->fresh()->load('plan'),
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $worker = $request->user();

        if ($worker->role !== 'worker') {
            return response()->json([
                'message' => 'هذا القسم متاح للصنايعي فقط.',
            ], 403);
        }

        return response()->json(
            Payment::with('subscription.plan')
                ->where('worker_id', $worker->id)
                ->latest()
                ->paginate(10)
        );
    }

    public function cancel(Request $request)
    {
        $worker = $request->user();

        $subscription = Subscription::where('worker_id', $worker->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'لا يوجد اشتراك فعال.',
            ], 422);
        }

        $subscription->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'تم إلغاء الاشتراك.',
            'subscription' => $subscription->fresh()->load('plan'),
        ]);
    }
}
