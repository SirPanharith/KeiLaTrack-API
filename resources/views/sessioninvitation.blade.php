<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Invitation</title>
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
        .session-box {
            border: 4px solid #ffffff; /* Thicker white border around the session box */
            padding: 2rem;
            border-radius: 10px;
            background-color: #4CAF50;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .info-item {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 1rem;
        }
        .info-item label {
            color: #ffffff;
            width: 150px; /* Fixed width to align all labels */
            text-align: left;
            padding-right: 10px;
        }
        .info-value {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1rem;
            border-radius: 50px;
            color: white;
            flex-grow: 1;
            text-align: left;
        }
        .btn {
            background-color: #388E3C;
            padding: 0.75rem 1rem;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
            width: 100%;
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
        .form-actions {
            display: flex;
            gap: 1rem;
        }
        .form-actions form {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="session-box">
        <h1 class="text-white text-4xl font-bold mb-2 text-center">{{ $team_name }} Football Team </br> has invite to a Session</h1>
        <p class="text-white text-sm mb-8 text-center">{{ $note }}</p>

        <div class="session-info">
            <div class="info-item">
                <label>Recorded Email:</label>
                <div class="info-value">{{ $email }}</div>
            </div>
            <div class="info-item">
                <label>Date:</label>
                <div class="info-value">{{ $date }}</div>
            </div>
            <div class="info-item">
                <label>Time:</label>
                <div class="info-value">{{ $time }}</div>
            </div>
            <div class="info-item">
                <label>Duration:</label>
                <div class="info-value">{{ $duration }}</div>
            </div>
            <div class="info-item">
                <label>Location:</label>
                <div class="info-value">{{ $location }}</div>
            </div>
            <!-- <div class="info-item">
                <label>Game Mode:</label>
                <div class="info-value">{{ $mode }}</div>
            </div> -->
        </div>

        <div class="form-actions">
            <form action="{{ url('/session-invitation/' . $token . '/accept') }}" method="POST">
                @csrf
                <button type="submit" class="btn accept text-white font-bold">Accept</button>
            </form>
            <form action="{{ url('/session-invitation/' . $token . '/reject') }}" method="POST">
                @csrf
                <button type="submit" class="btn reject text-white font-bold">Reject</button>
            </form>
        </div>
    </div>
</body>
</html>
