<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Klas: {{ $class->name }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <div class="bg-gray-800/80 rounded-lg p-6 border border-gray-700">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div>
                        <h3 class="text-lg font-semibold">Studenten in {{ $class->name }}</h3>
                        <p class="text-sm text-gray-400 mt-1">Resultaten voor {{ $forDocent ? 'jouw vragen' : 'alle vragen (admin)' }}.</p>
                    </div>
                    @if($forDocent)
                        <form method="POST" action="{{ route('classes.reset_grades', $class) }}" onsubmit="return confirm('Dit zet alle juist/fout waardes voor deze klas terug (behoudt antwoorden). Weet je zeker dat je wilt doorgaan?');" class="shrink-0">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded bg-yellow-600 hover:bg-yellow-700 text-xs font-medium">Reset tellingen</button>
                        </form>
                    @endif
                </div>
                @if($forDocent)
                    <div class="text-xs text-gray-500 mb-4">Gebruik <span class="text-gray-300 font-medium">Reset tellingen</span> na het toekennen van extra punten om een nieuwe beoordelingsronde te starten. Antwoorden vóór het laatst reset-moment worden genegeerd in deze teller (antwoorden blijven wel bewaard).</div>
                @else
                    <div class="text-xs text-gray-500 mb-4">Admin overzicht toont alle historische antwoorden; resetten is alleen beschikbaar voor docenten.</div>
                @endif
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
