        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-800 text-red-100 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Class Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col items-center px-4 py-8">
    <div class="max-w-4xl w-full">
        <h1 class="text-4xl font-bold mb-6 text-center">Admin Dashboard</h1>
        <p class="mb-8 text-gray-400 text-center">Beheer docenten, klassen en studenten.</p>

        <!-- Add Docent Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Docent toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'docent']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="docent">
                <input type="text" name="name" placeholder="Naam docent" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="email" name="email" placeholder="E-mail docent" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <div class="flex flex-wrap gap-2">
                    @foreach($classes as $class)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="class_id[]" value="{{ $class->id }}" class="form-checkbox text-indigo-600 bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2">{{ $class->name }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">Toevoegen</button>
            </form>
        </div>

        <!-- Add Class Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Klas toevoegen</h2>
            <form method="POST" action="{{ route('classes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="student">
                <input type="text" name="class_name" placeholder="Naam klas" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">Toevoegen</button>
            </form>
        </div>

        <!-- Add Student Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Student toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'student']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="student">
                <input type="text" name="name" placeholder="Naam student" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="email" name="email" placeholder="E-mail student" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <div class="flex flex-wrap gap-2">
                    @foreach($classes as $class)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="class_id[]" value="{{ $class->id }}" class="form-checkbox text-indigo-600 bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2">{{ $class->name }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">Toevoegen</button>
            </form>
        </div>

                <!-- Add Admin Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Admin toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'admin']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="admin">
                <input type="text" name="name" placeholder="Naam admin" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="email" name="email" placeholder="E-mail admin" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">Toevoegen</button>
            </form>
        </div>

        <!-- Manage Accounts Section -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Accounts beheren</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-gray-300">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Naam</th>
                            <th class="px-4 py-2">E-mail</th>
                            <th class="px-4 py-2">Rol</th>
                            <th class="px-4 py-2">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ ucfirst($user->role) }}</td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-400 hover:underline">Bewerken</a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
