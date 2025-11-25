<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Resultaten: Vraag #{{ $question->id }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-2xl font-semibold mb-2">Vraag</h3>
                <p class="text-gray-100 whitespace-pre-line break-words">{{ $question->content }}</p>
                <div class="mt-2 text-xs text-gray-400">
                    Type: <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
                </div>
            </div>

            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="_" value="1">
                    <div>
                        <label class="block text-sm mb-1">Filter op klas</label>
                        <select name="class_id" class="px-3 py-2 rounded bg-gray-700 border border-gray-600" onchange="this.form.submit()">
                            <option value="">Alle klassen</option>
                            @php $classesList = $classes instanceof \Illuminate\Support\Collection ? $classes : (is_array($classes) ? collect($classes) : collect()); @endphp
                            @foreach($classesList as $class)
                                @if(is_object($class))
                                <option value="{{ $class->id }}" @if($selectedClassId==$class->id) selected @endif>{{ $class->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('docent.questions.index') }}" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700">Terug</a>
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
                                @if($question->type==='open')
                                <th class="px-3 py-2">Beoordeling</th>
                                @endif
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
                                <tr class="hover:bg-gray-700/40" x-data="{showQ:false, showA:false}">
                                    <td class="px-3 py-2">{{ optional($row['user'])->name ?? 'Onbekend' }}</td>
                                    <td class="px-3 py-2">
                                        <div>
                                            @php $qFull = $question->content; $qLimit = 60; @endphp
                                            @if(Str::length($qFull) > $qLimit)
                                                <button type="button" class="text-left w-full group" @click="showQ=!showQ">
                                                    <span class="block line-clamp-2 break-words group-hover:underline" x-show="!showQ">{{ Str::limit($qFull, $qLimit) }}</span>
                                                    <span class="block whitespace-pre-line break-words" x-show="showQ">{{ $qFull }}</span>
                                                    <span class="text-xs text-indigo-300" x-show="!showQ">Meer...</span>
                                                </button>
                                            @else
                                                <span class="block break-words whitespace-pre-line">{{ $qFull }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($question->type==='multiple_choice')
                                            <span class="break-words">{{ optional(optional($ans)->choice)->label }}@if(optional(optional($ans)->choice)->label). @endif {{ optional(optional($ans)->choice)->text }}</span>
                                        @else
                                            @if($ans)
                                            <div>
                                                @php $aFull = $ans->answer_text; $aLimit = 60; @endphp
                                                @if(Str::length($aFull) > $aLimit)
                                                    <button type="button" class="text-left w-full group" @click="showA=!showA">
                                                        <span class="block line-clamp-2 break-words group-hover:underline" x-show="!showA">{{ Str::limit($aFull, $aLimit) }}</span>
                                                        <span class="block whitespace-pre-line break-words" x-show="showA">{{ $aFull }}</span>
                                                        <span class="text-xs text-indigo-300" x-show="!showA">Meer...</span>
                                                    </button>
                                                @else
                                                    <span class="block break-words whitespace-pre-line">{{ $aFull }}</span>
                                                @endif
                                            </div>
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-semibold {{ $color }}">{{ $symbol }}</td>
                                    <td class="px-3 py-2 text-xs text-gray-400">{{ optional(optional($ans)->created_at)->diffForHumans() }}</td>
                                    @if($question->type==='open')
                                    <td class="px-3 py-2">
                                        @if($ans)
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('docent.questions.grade', $question) }}">
                                                @csrf
                                                <input type="hidden" name="answer_id" value="{{ $ans->id }}">
                                                <input type="hidden" name="is_correct" value="1">
                                                <button class="px-2 py-1 text-xs rounded {{ $ans->is_correct===1 ? 'bg-emerald-700 text-white' : 'bg-gray-700 hover:bg-emerald-700' }}">✓ Juist</button>
                                            </form>
                                            <form method="POST" action="{{ route('docent.questions.grade', $question) }}">
                                                @csrf
                                                <input type="hidden" name="answer_id" value="{{ $ans->id }}">
                                                <input type="hidden" name="is_correct" value="0">
                                                <button class="px-2 py-1 text-xs rounded {{ $ans->is_correct===0 ? 'bg-red-700 text-white' : 'bg-gray-700 hover:bg-red-700' }}">✗ Onjuist</button>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                    @endif
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
