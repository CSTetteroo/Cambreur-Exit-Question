<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Student: {{ $student->name }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <div class="bg-gray-800/80 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold mb-2">{{ $student->name }} — {{ $class->name }}</h3>
                <div class="text-sm text-gray-400 mb-4">Resultaten voor {{ $forDocent ? 'jouw vragen' : 'alle vragen (admin)' }}.</div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="p-3 rounded bg-gray-800/60 text-center">
                        <div class="text-sm text-gray-400">Juist</div>
                        <div class="text-2xl font-semibold text-emerald-400">{{ $correct }}</div>
                    </div>
                    <div class="p-3 rounded bg-gray-800/60 text-center">
                        <div class="text-sm text-gray-400">Fout</div>
                        <div class="text-2xl font-semibold text-red-400">{{ $wrong }}</div>
                    </div>
                    <div class="p-3 rounded bg-gray-800/60 text-center">
                        <div class="text-sm text-gray-400">Totaal antwoorden</div>
                        <div class="text-2xl font-semibold text-gray-100">{{ $answers->count() }}</div>
                    </div>
                </div>

                <h4 class="text-md font-semibold mb-2">Antwoorden</h4>
                @if($answers->isEmpty())
                    <p class="text-gray-400">Geen antwoorden gevonden voor deze selectie.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-gray-300">
                            <thead class="bg-gray-700/60">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Vraag</th>
                                    <th class="px-4 py-2 font-medium">Antwoord</th>
                                    <th class="px-4 py-2 font-medium">Correct</th>
                                    <th class="px-4 py-2 font-medium">Datum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/70">
                                @foreach($answers as $ans)
                                    <tr class="hover:bg-gray-700/30">
                                        <td class="px-4 py-2">{{ Str::limit(optional($ans->question)->content, 120) }}</td>
                                        <td class="px-4 py-2">@if(optional($ans->choice)) {{ optional($ans->choice)->label }}. {{ optional($ans->choice)->text }} @else {{ $ans->answer_text }} @endif</td>
                                        <td class="px-4 py-2">@if($ans->is_correct === 1) <span class="text-emerald-400">✓</span> @elseif($ans->is_correct === 0) <span class="text-red-400">✗</span> @else <span class="text-gray-400">–</span> @endif</td>
                                        <td class="px-4 py-2 text-xs text-gray-400">{{ $ans->created_at->diffForHumans() }}</td>
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
