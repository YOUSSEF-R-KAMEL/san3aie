<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerFeature;
use Illuminate\Http\Request;

class FeaturedWorkerController extends Controller
{
    public function index()
    {
        $workers = User::with(['category', 'area'])
            ->where('role', 'worker')
            ->where('is_blocked', false)
            ->whereHas('features', function ($query) {
                $query->where('feature', 'featured')
                    ->where('expires_at', '>', now());
            })
            ->get()
            ->map(function ($worker) {
                $feature = $worker->features()
                    ->where('feature', 'featured')
                    ->where('expires_at', '>', now())
                    ->latest('expires_at')
                    ->first();

                $worker->is_featured = true;
                $worker->featured_until = $feature?->expires_at;

                return $worker;
            });

        return response()->json($workers);
    }

    public function myFeature(Request $request)
    {
        $worker = $request->user();

        if ($worker->role !== 'worker') {
            return response()->json([
                'message' => 'هذا القسم متاح للصنايعي فقط.',
            ], 403);
        }

        $feature = WorkerFeature::where('worker_id', $worker->id)
            ->where('feature', 'featured')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return response()->json([
            'is_featured' => (bool) $feature,
            'feature' => $feature,
        ]);
    }
}
