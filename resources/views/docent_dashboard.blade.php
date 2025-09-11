<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-5xl mx-auto px-4">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-800/80 border border-red-600 text-red-100 rounded">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="mb-8 text-gray-400 text-center">Beheer docenten, klassen, studenten en admins.</p>

        <!-- Add Docent Form -->
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
            <h2 class="text-2xl font-semibold mb-4">Docent toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'docent']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="docent">
                <input type="text" name="name" placeholder="Naam docent" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="email" name="email" placeholder="E-mail docent" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <div class="flex flex-wrap gap-3">
                    @if(is_iterable($classes))
                        @foreach($classes as $class)
                            @if(is_object($class))
                                <label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
                                    <input type="checkbox" name="class_id[]" value="{{ $class->id }}" class="form-checkbox text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded">
                                    <span class="ml-2">{{ $class->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    @endif
                </div>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition text-sm font-medium">Toevoegen</button>
            </form>
        </div>

        <!-- Add Class Form -->
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
            <h2 class="text-2xl font-semibold mb-4">Klas toevoegen</h2>
            <form method="POST" action="{{ route('classes.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="student">
                <input type="text" name="class_name" placeholder="Naam klas" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition text-sm font-medium">Toevoegen</button>
            </form>
        </div>

        <!-- Add Student Form -->
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
            <h2 class="text-2xl font-semibold mb-4">Student toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'student']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="student">
                <input type="text" name="name" placeholder="Naam student" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="email" name="email" placeholder="E-mail student" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <div class="flex flex-wrap gap-3">
                    @if(is_iterable($classes))
                        @foreach($classes as $class)
                            @if(is_object($class))
                                <label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
                                    <input type="checkbox" name="class_id[]" value="{{ $class->id }}" class="form-checkbox text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded">
                                    <span class="ml-2">{{ $class->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    @endif
                </div>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition text-sm font-medium">Toevoegen</button>
            </form>
        </div>

        <!-- Add Admin Form -->
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
            <h2 class="text-2xl font-semibold mb-4">Admin toevoegen</h2>
            <form method="POST" action="{{ route('users.store', ['role' => 'admin']) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="admin">
                <input type="text" name="name" placeholder="Naam admin" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="email" name="email" placeholder="E-mail admin" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <input type="password" name="password" placeholder="Wachtwoord" class="w-full px-4 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring focus:ring-indigo-600/40" required>
                <button type="submit" class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition text-sm font-medium">Toevoegen</button>
            </form>
        </div>

        <!-- Manage Accounts Section -->
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8 border border-gray-700">
            <h2 class="text-2xl font-semibold mb-4">Accounts beheren</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-gray-300 text-sm">
                    <thead class="bg-gray-700/60">
                        <tr class="text-gray-200">
                            <th class="px-4 py-2 font-medium">Naam</th>
                            <th class="px-4 py-2 font-medium">E-mail</th>
                            <th class="px-4 py-2 font-medium">Rol</th>
                            <th class="px-4 py-2 font-medium">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/70">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-700/40 transition">
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                <td class="px-4 py-2">{{ ucfirst($user->role) }}</td>
                                <td class="px-4 py-2 space-x-3">
                                    <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-400 hover:text-indigo-300">Bewerken</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijderen</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
