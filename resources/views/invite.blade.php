<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            background-color: #66bb6a; /* Lighter shade of green */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .bg-custom-green {
            background-color: #4CAF50;
        }
        .input-group {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1rem;
            border-radius: 50px;
            transition: background-color 0.3s ease-in-out;
        }
        .input-group input,
        .input-group select {
            background-color: #388E3C; /* Green background */
            color: white; /* White text */
            border: none;
            outline: none;
            padding: 0.5rem;
            width: 100%;
            border-radius: 5px;
            appearance: none;
        }
        .input-group select option {
            background-color: #388E3C; /* Green background for options */
            color: white; /* White text for options */
        }
        .input-group:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .btn {
            background-color: #388E3C;
            padding: 0.75rem 1rem;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn:hover {
            background-color: #2E7D32;
            transform: translateY(-3px);
        }
        .btn:active {
            background-color: #1B5E20;
            transform: translateY(1px);
        }
        .btn.reject {
            background-color: #d32f2f;
        }
        .btn.reject:hover {
            background-color: #c62828;
        }
        .btn.reject:active {
            background-color: #b71c1c;
        }
        .form-section {
            border: 4px solid #ffffff; /* Thicker white border around the form */
            padding: 2rem;
            border-radius: 10px;
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-sm mx-auto bg-custom-green rounded-lg shadow-lg form-section">
        <h1 class="text-white text-4xl font-bold mb-2 text-center">Invitation from</br>{{ $team_name }}</br>Football Team</h1>
        <p class="text-white text-sm mb-8 text-center">{{ $team_detail }}</p>

        <form action="{{ url('/accept-invitation/' . $token) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-white text-sm mb-2" for="email">Recorded Email:</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" value="{{ $email }}" readonly>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-white text-sm mb-2" for="name">Name:</label>
                <div class="input-group">
                    <input type="text" id="name" name="name" value="{{ $name }}" readonly>
                </div>
            </div>
            <input type="hidden" name="player_id" value="{{ $player_id }}">
            <input type="hidden" name="team_id" value="{{ $team_id }}">
            <input type="hidden" name="invitation_id" value="{{ $invitation_id }}">
            <div class="mb-4">
                <label class="block text-white text-sm mb-2" for="primaryPosition">Primary Position:</label>
                <div class="input-group">
                    <select id="primaryPosition" name="primaryPosition">
                        <option value="1">Striker</option>
                        <option value="2">Midfielder</option>
                        <option value="3">Defender</option>
                        <option value="4">Goalkeeper</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-white text-sm mb-2" for="secondaryPosition">Secondary Position:</label>
                <div class="input-group">
                    <select id="secondaryPosition" name="secondaryPosition">
                        <option value="1">Striker</option>
                        <option value="2">Midfielder</option>
                        <option value="3">Defender</option>
                        <option value="4">Goalkeeper</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-between">
                <button type="submit" class="btn accept text-white font-bold">Accept</button>
                <!-- <button type="button" class="btn reject text-white font-bold">Reject</button> -->
            </div>
        </form>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
