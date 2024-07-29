<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="email" value="{{ session('email') }}">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <input type="password" name="password" placeholder="Enter new password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
    <button type="submit">Reset Password</button>
</form>
