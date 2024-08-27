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
            background-color: transparent;
            border: none;
            outline: none;
            color: white;
            width: 100%;
        }
        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
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

        .input-group select {
        background-color: #388E3C; /* Green background */
        color: white; /* White text */
        border: none;
        padding: 0.5rem;
        border-radius: 50px;
        outline: none;
        width: 100%;
        appearance: none;
    }

    .input-group select:focus {
        background-color: #4CAF50; /* Slightly lighter green on focus */
    }
    </style>
</head>
<body>
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

<div class="mb-4">
    <label class="block text-white text-sm mb-2" for="secondaryPosition">Secondary Position:</label>
    <div class="input-group relative">
        <select id="secondaryPosition" name="secondaryPosition" class="block appearance-none w-full bg-transparent border-none text-white py-2 px-3 pr-8 leading-tight focus:outline-none focus:ring-0">
            <option value="1" class="bg-custom-green">Striker</option>
            <option value="2" class="bg-custom-green">Midfielder</option>
            <option value="3" class="bg-custom-green">Defender</option>
            <option value="4" class="bg-custom-green">Goalkeeper</option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
        </div>
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
