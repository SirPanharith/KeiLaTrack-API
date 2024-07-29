<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\PlayerInfo;
use App\Models\Host;
use Carbon\Carbon;
use App\Mail\OtpMail;

class ForgotPasswordController extends Controller
{
    // Forgot Password for PlayerInfo
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:PlayerInfo,Player_Email']);

        $otp = rand(100000, 999999);
        $email = $request->email;

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        Mail::to($email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP has been sent to your email address'], 200);
    }

    // Reset Password for PlayerInfo
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:PlayerInfo,Player_Email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otpData = DB::table('password_resets')
            ->where([
                ['email', $request->email],
                ['token', $request->otp]
            ])
            ->first();

        if (!$otpData) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        PlayerInfo::where('Player_Email', $request->email)->update(['Player_Password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return response()->json(['message' => 'Your password has been changed!'], 200);
    }

    // Forgot Password for Host
    public function submitForgetHostPasswordForm(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:Host,Host_Email']);

        $otp = rand(100000, 999999);
        $email = $request->email;

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        Mail::to($email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP has been sent to your email address'], 200);
    }

    // Reset Password for Host
    public function submitResetHostPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:Host,Host_Email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otpData = DB::table('password_resets')
            ->where([
                ['email', $request->email],
                ['token', $request->otp]
            ])
            ->first();

        if (!$otpData) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        Host::where('Host_Email', $request->email)->update(['Host_Password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return response()->json(['message' => 'Your password has been changed!'], 200);
    }
}
