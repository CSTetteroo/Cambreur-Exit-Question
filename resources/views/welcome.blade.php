<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welkom bij Class Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col justify-center items-center px-4">

    <div class="max-w-md w-full text-center">
        <h1 class="text-4xl font-bold mb-6">Welkom bij Class Quiz!</h1>
        <p class="mb-8 text-gray-400">Test je kennis en daag je klasgenoten uit.</p>

        <div class="space-x-4">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-block px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="inline-block px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">
                    Inloggen
                </a>
            @endauth
        </div>
    </div>

</body>
</html>
