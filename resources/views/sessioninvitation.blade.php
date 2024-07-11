<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Invitation</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You have been Invited to a Session</h1>
        </div>
        <div class="session-invitation">
            <p>Let's come play together.</p>
        </div>
        <div class="session-info">
            <div class="info-item">
                <label><strong>Recorded Email:</strong></label>
                <div class="info-value">{{ $email }}</div>
            </div>
            <div class="info-item">
                <label><strong>Date:</strong></label>
                <div class="info-value">{{ $date }}</div>
            </div>
            <div class="info-item">
                <label><strong>Time:</strong></label>
                <div class="info-value">{{ $time }}</div>
            </div>
            <div class="info-item">
                <label><strong>Duration:</strong></label>
                <div class="info-value">{{ $duration }}</div>
            </div>
            <div class="info-item">
                <label><strong>Location:</strong></label>
                <div class="info-value">{{ $location }}</div>
            </div>
            <div class="info-item">
                <label><strong>Mode:</strong></label>
                <div class="info-value">{{ $mode }}</div>
            </div>
        </div>
        <div class="form-actions">
            <form action="{{ url('/session-invitation/' . $token . '/accept') }}" method="POST">
                @csrf
                <button type="submit" class="btn accept">Accept</button>
            </form>
            <form action="{{ url('/session-invitation/' . $token . '/reject') }}" method="POST">
                @csrf
                <button type="submit" class="btn reject">Reject</button>
            </form>
        </div>
    </div>
</body>
</html>
