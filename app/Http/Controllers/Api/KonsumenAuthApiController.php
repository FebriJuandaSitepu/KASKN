<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class KonsumenAuthApiController extends Controller
{
    /**
     * ✅ SIMPLE: Login untuk KONSUMEN menggunakan User model untuk token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find konsumen by email
            $konsumen = Konsumen::where('email', $request->email)->first();

            if (!$konsumen || !Hash::check($request->password, $konsumen->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // ✅ WORKAROUND: Create or find user with same email for token generation
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $konsumen->nama,
                    'email' => $konsumen->email,
                    'password' => $konsumen->password, // Same password hash
                    'role' => 'konsumen',
                ]);
            }

            // Create token using User model
            $token = $user->createToken('konsumen-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'konsumen' => $konsumen,
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Login gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SIMPLE: Register untuk KONSUMEN
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:konsumens,email',
            'password' => 'required|string|min:6',
            'no_telepon' => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $passwordHash = Hash::make($request->password);

            // Create new konsumen
            $konsumen = Konsumen::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => $passwordHash,
                'no_telepon' => $request->no_telepon,
                'saldo' => 0,
            ]);

            // ✅ WORKAROUND: Also create user for future logins
            User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => $passwordHash,
                'role' => 'konsumen',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Pendaftaran berhasil',
                'data' => [
                    'konsumen' => $konsumen,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pendaftaran gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get konsumen profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            $konsumen = Konsumen::where('email', $user->email)->first();

            if (!$konsumen) {
                return response()->json([
                    'status' => false,
                    'message' => 'Konsumen tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profil berhasil diambil',
                'data' => $konsumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update konsumen profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'no_telepon' => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $konsumen = Konsumen::where('email', $user->email)->first();

            if (!$konsumen) {
                return response()->json([
                    'status' => false,
                    'message' => 'Konsumen tidak ditemukan'
                ], 404);
            }

            $konsumen->update($validator->validated());

            // Also update user
            $user->update([
                'name' => $konsumen->nama
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $konsumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout konsumen
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}