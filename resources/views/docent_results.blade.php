<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Resultaten: Vraag #{{ $question->id }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-2xl font-semibold mb-2">Vraag</h3>
                <p class="text-gray-100 whitespace-pre-line">{{ $question->content }}</p>
                <div class="mt-2 text-xs text-gray-400">
                    Type: <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
                </div>
            </div>

            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="_" value="1">
                    <div>
                        <label class="block text-sm mb-1">Filter op klas</label>
                        <select name="class_id" class="px-3 py-2 rounded bg-gray-700 border border-gray-600">
                            <option value="">Alle klassen</option>
                            @if(is_iterable($classes))
                                @foreach($classes as $class)
                                    @if(is_object($class))
                                    <option value="{{ $class->id }}" @if($selectedClassId==$class->id) selected @endif>{{ $class->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <button class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700">Toepassen</button>
                    <a href="{{ route('docent.questions.index') }}" class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-600">Terug</a>
                </form>
            </div>

            @if($question->type === 'multiple_choice')
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-xl font-semibold mb-3">Verdeling van antwoorden</h3>
                @if($distribution && $distribution->isNotEmpty())
                    <ul class="space-y-1 text-sm">
                        @foreach($distribution as $label => $count)
                            <li class="flex items-center justify-between">
                                <span class="text-gray-300">Optie {{ $label }}</span>
                                <span class="text-gray-100 font-medium">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-400 text-sm">Nog geen antwoorden.</p>
                @endif
            </div>
            @endif

            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xl font-semibold">Antwoorden</h3>
                    <span class="text-xs text-gray-400">@if($selectedClassId) Klas-weergave (alle studenten) @else Alle studenten @endif</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-gray-300">
                        <thead class="bg-gray-700/60 text-gray-200">
                            <tr>
                                <th class="px-3 py-2">Student</th>
                                <th class="px-3 py-2">Vraag</th>
                                <th class="px-3 py-2">Antwoord</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Datum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/70">
                            @forelse($rows as $row)
                                @php
                                    $ans = $row['answer'] ?? null;
                                    $status = $row['status'] ?? 'neutral';
                                    $symbol = $status==='correct' ? '✓' : ($status==='wrong' ? '✗' : '–');
                                    $color = $status==='correct' ? 'text-emerald-400' : ($status==='wrong' ? 'text-red-400' : 'text-gray-400');
                                @endphp
                                <tr class="hover:bg-gray-700/40">
                                    <td class="px-3 py-2">{{ optional($row['user'])->name ?? 'Onbekend' }}</td>
                                    <td class="px-3 py-2">{{ Str::limit($question->content, 60) }}</td>
                                    <td class="px-3 py-2">
                                        @if($question->type==='multiple_choice')
                                            {{ optional(optional($ans)->choice)->label }}@if(optional(optional($ans)->choice)->label). @endif {{ optional(optional($ans)->choice)->text }}
                                        @else
                                            <span class="whitespace-pre-line">{{ optional($ans)->answer_text }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-semibold {{ $color }}">{{ $symbol }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-400">{{ optional(optional($ans)->created_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-3 text-gray-500">Geen gegevens beschikbaar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
