
<!DOCTYPE html>
<html lang="nl" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gebruiker bewerken</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col items-center px-4 py-8">
    <div class="max-w-lg w-full">
        <h1 class="text-3xl font-bold mb-6 text-center">Gebruiker bewerken</h1>
        <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Naam" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="E-mail" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
            <input type="password" name="password" placeholder="Nieuw wachtwoord (optioneel)" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100">
            <select name="role" id="role-select" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100" required>
                <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
                <option value="docent" @if($user->role=='docent') selected @endif>Docent</option>
                <option value="student" @if($user->role=='student') selected @endif>Student</option>
            </select>
            <div id="class-section" class="mt-2 @if(!in_array($user->role,['student','docent'])) hidden @endif">
                <label class="block mb-2">Koppeling aan klassen:</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($classes as $class)
                        @if(is_object($class))
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="class_id[]" value="{{ $class->id }}" class="form-checkbox text-indigo-600 bg-gray-700 border-gray-600 rounded" @if($user->classes->contains($class->id)) checked @endif>
                            <span class="ml-2">{{ $class->name }}</span>
                        </label>
                        @endif
                    @endforeach
                </div>
            </div>
            <button type="submit" class="w-full px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition">Opslaan</button>
        </form>
        <div class="mt-6 text-center">
            <a href="{{ route('dashboard') }}" class="text-indigo-400 hover:underline">Terug naar dashboard</a>
        </div>
    </div>
    <script>
        const roleSelect = document.getElementById('role-select');
        const classSection = document.getElementById('class-section');
        function toggleClassSection(){
            if(['student','docent'].includes(roleSelect.value)){
                classSection.classList.remove('hidden');
            } else {
                classSection.classList.add('hidden');
                // Optionally uncheck all when hiding
                classSection.querySelectorAll('input[type=checkbox]').forEach(cb=>cb.checked=false);
            }
        }
        roleSelect.addEventListener('change', toggleClassSection);
    </script>
</body>
</html>
