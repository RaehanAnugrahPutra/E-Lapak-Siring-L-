<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User berhasil didaftarkan',
            'user' => $user
        ], 201);
    }

    //login api json
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Login gagal'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }

        //logout api json
        public function logout(Request $request)
        {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Berhasil logout'
            ]);
        }


    public function forgotPassword(Request $request)
    {
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // hapus request lama untuk email ini
    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    // generate kode 6 digit
    $token = random_int(100000, 999999);

    // simpan ke tabel
    DB::table('password_reset_tokens')->insert([
        'email'      => $request->email,
        'token'      => $token,
        'created_at' => Carbon::now(),
    ]);

    // kirim email sederhana
    Mail::raw(
        "Kode reset password Anda adalah: {$token}. Jangan berikan kode ini kepada siapa pun.",
        function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode Reset Password');
        }
    );

    return response()->json([
        'success' => true,
        'message' => 'Kode reset password telah dikirim ke email Anda.',
    ]);
}

    //verifikasi dan reset password
    public function verifyResetToken(Request $request)
    {
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required|digits:6',
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$record) {
        return response()->json([
            'success' => false,
            'message' => 'Kode reset tidak valid.',
        ], 400);
    }

    // Cek masa berlaku
    if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
        return response()->json([
            'success' => false,
            'message' => 'Kode reset sudah kedaluwarsa.',
        ], 400);
    }

    // JANGAN HAPUS TOKEN DI SINI!
    // Token akan dihapus nanti saat resetPassword

    return response()->json([
        'success' => true,
        'message' => 'Kode reset valid.',
    ], 200);
    }

    public function resetPassword(Request $request)
    {
    $request->validate([
        'email'                 => 'required|email|exists:users,email',
        'token'                 => 'required|digits:6',
        'password'              => 'required|string|min:8|confirmed',
        // kirim juga field password_confirmation dari frontend
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (! $record) {
        return response()->json([
            'success' => false,
            'message' => 'Kode reset tidak valid.',
        ], 400);
    }

    // (opsional) cek masa berlaku, misal 15 menit
    if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
        return response()->json([
            'success' => false,
            'message' => 'Kode reset sudah kedaluwarsa.',
        ], 400);
    }

    // update password user
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // hapus token reset supaya tidak bisa dipakai lagi
    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    // (opsional) hapus semua token Sanctum user supaya harus login ulang
    $user->tokens()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil direset. Silakan login dengan password baru.',
    ]);
    }

}

