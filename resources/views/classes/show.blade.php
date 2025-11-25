<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Klas: {{ $class->name }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <div class="bg-gray-800/80 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold mb-2">Studenten in {{ $class->name }}</h3>
                <p class="text-sm text-gray-400 mb-4">Tonen van resultaten voor {{ $forDocent ? 'jouw vragen' : 'alle vragen (admin)' }}.</p>
                @if($students->isEmpty())
                    <p class="text-gray-400">Geen studenten in deze klas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-gray-300">
                            <thead class="bg-gray-700/60">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Student</th>
                                    <th class="px-4 py-2 font-medium">Juist</th>
                                    <th class="px-4 py-2 font-medium">Fout</th>
                                    <th class="px-4 py-2 font-medium">Acties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/70">
                                @foreach($students as $stu)
                                    @php $c = $counts[$stu->id] ?? ['correct'=>0,'wrong'=>0]; @endphp
                                    <tr class="hover:bg-gray-700/30">
                                        <td class="px-4 py-2">{{ $stu->name }}</td>
                                        <td class="px-4 py-2 font-medium text-emerald-400">{{ $c['correct'] ?? 0 }}</td>
                                        <td class="px-4 py-2 font-medium text-red-400">{{ $c['wrong'] ?? 0 }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('classes.student.show', ['class' => $class->id, 'user' => $stu->id]) }}" class="text-indigo-300 hover:text-indigo-200 text-sm">Bekijk details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
