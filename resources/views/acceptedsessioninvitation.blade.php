<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Invitation Accepted</title>
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
        .success-box {
            border: 4px solid #ffffff; /* Thicker white border around the success box */
            padding: 2rem;
            border-radius: 10px;
            background-color: #4CAF50;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        .success-box h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .success-box p {
            color: #ffffff;
            font-size: 1.25rem;
            margin-bottom: 2rem;
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
    </style>
</head>
<body>
    <div class="success-box">
        <h1>Invitation Accepted!</h1>
        <p>Thank you for confirming your participation in the upcoming session. We look forward to seeing you there!</p>
        <!-- <a href="{{ url('/') }}" class="btn text-white font-bold">Go to Home</a> -->
    </div>
</body>
</html>
