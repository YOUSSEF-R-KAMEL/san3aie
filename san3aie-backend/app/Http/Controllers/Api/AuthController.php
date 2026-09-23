<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:worker,customer'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:70'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        if ($validated['role'] === 'worker' && empty($validated['category_id'])) {
            return response()->json([
                'message' => 'من فضلك اختر المهنة.',
                'errors' => [
                    'category_id' => ['من فضلك اختر المهنة.'],
                ],
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'category_id' => $validated['role'] === 'worker'
                ? $validated['category_id']
                : null,
            'area_id' => $validated['area_id'],
            'bio' => $validated['role'] === 'worker'
                ? ($validated['bio'] ?? null)
                : null,
            'experience_years' => $validated['role'] === 'worker'
                ? ($validated['experience_years'] ?? null)
                : null,
            'is_available' => $validated['role'] === 'worker'
                ? ($validated['is_available'] ?? true)
                : false,
        ]);

        $token = $user->createToken('san3aie-token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح',
            'user' => $user->load(['category', 'area']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with(['category', 'area'])
            ->where('phone', $validated['phone'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['رقم الهاتف أو كلمة المرور غير صحيحة.'],
            ]);
        }

        $token = $user->createToken('san3aie-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['category', 'area']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
