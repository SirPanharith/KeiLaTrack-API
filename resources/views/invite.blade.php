<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Team Invitation</h1>
        </div>
        <div class="team-info">
            <p>This is the best team in the world.</p>
        </div>
        <div class="form-section">
            <form action="{{ url('/accept-invitation/' . $token) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Recorded Email:</label>
                    <input type="email" id="email" name="email" value="{{ $email }}" readonly>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="{{ $name }}" readonly>
                </div>
                <input type="hidden" name="player_id" value="{{ $player_id }}">
                <input type="hidden" name="team_id" value="{{ $team_id }}">
                <input type="hidden" name="invitation_id" value="{{ $invitation_id }}">
                <div class="form-group">
                    <label for="primaryPosition">Primary Position:</label>
                    <select id="primaryPosition" name="primaryPosition">
                        <option value="1">Striker</option>
                        <option value="2">Midfielder</option>
                        <option value="3">Defender</option>
                        <option value="4">Goalkeeper</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="secondaryPosition">Secondary Position:</label>
                    <select id="secondaryPosition" name="secondaryPosition">
                        <option value="1">Striker</option>
                        <option value="2">Midfielder</option>
                        <option value="3">Defender</option>
                        <option value="4">Goalkeeper</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn accept">Accept</button>
                    <button type="button" class="btn reject">Reject</button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
