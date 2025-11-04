<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">Docent: Vragen beheren</h2>
    </x-slot>

    <style>[x-cloak]{display:none!important}</style>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            @if (session('status'))
                <div class="p-3 rounded bg-green-800/60 border border-green-700 text-green-100">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded bg-red-800/60 border border-red-700 text-red-100">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Create question -->
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-2xl font-semibold mb-4">Nieuwe vraag</h3>
                <form method="POST" action="{{ route('docent.questions.store') }}" x-data="{
                        type: 'open',
                        choices: ['', ''],
                        correct: null,
                        addChoice(){ if(this.choices.length < 4) this.choices.push(''); }
                    }" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm mb-1">Type</label>
                        <select name="type" x-model="type" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600">
                            <option value="open">Open</option>
                            <option value="multiple_choice">Meerkeuze</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Inhoud</label>
                        <textarea name="content" class="w-full min-h-24 px-3 py-2 rounded bg-gray-700 border border-gray-600" required>{{ old('content') }}</textarea>
                    </div>
                    <template x-if="type==='multiple_choice'">
                        <div>
                            <label class="block text-sm mb-2">Opties</label>
                            <div class="space-y-2">
                                <template x-for="(c,i) in choices" :key="i">
                                    <div class="flex items-center gap-2">
                                        <input :name="`choices[`+i+`]`" x-model="choices[i]" type="text" class="flex-1 px-3 py-2 rounded bg-gray-700 border border-gray-600" :placeholder="`Optie ${String.fromCharCode(65+i)}`" :required="i < 2">
                                        <label class="inline-flex items-center text-xs text-gray-300">
                                            <input type="radio" name="correct_choice" :value="i" x-model.number="correct" class="form-radio text-emerald-500 bg-gray-800 border-gray-600">
                                            <span class="ml-1">Juist</span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <button type="button" @click="addChoice()" class="px-3 py-1.5 rounded bg-gray-700 hover:bg-gray-600 text-sm disabled:opacity-50" :disabled="choices.length>=4">Optie toevoegen</button>
                                <span class="text-xs text-gray-400">Minimaal 2 opties, maximaal 4</span>
                            </div>
                        </div>
                    </template>
                    <div>
                        <label class="block text-sm mb-2">Activeer direct voor klassen (optioneel)</label>
                        @php($classesList = $classes instanceof \Illuminate\Support\Collection ? $classes : (is_array($classes) ? collect($classes) : collect()))
                        <div class="flex flex-wrap gap-2">
                            @foreach($classesList as $class)
                                @if(is_object($class))
                                <label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
                                    <input type="checkbox" name="activate_class_ids[]" value="{{ $class->id }}" class="form-checkbox text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded">
                                    <span class="ml-2">{{ $class->name }}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <button class="px-6 py-2 rounded bg-indigo-600 hover:bg-indigo-700">Opslaan</button>
                </form>
            </div>

            <!-- Questions list and activate -->
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-2xl font-semibold mb-4">Mijn vragen</h3>
                @if($questions->isEmpty())
                    <p class="text-gray-400">Nog geen vragen.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Details</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Vraag</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Opties</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Antwoorden</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Acties</th>
                                </tr>
                            </thead>
                            @foreach($questions as $q)
                            <tbody x-data="{ open: false }" class="divide-y divide-gray-700">
                                <tr class="bg-gray-800/40">
                                    <td class="px-4 py-3 align-top">
                                        <button @click.stop="open = !open" type="button" class="inline-flex items-center px-2 py-1 text-xs rounded bg-gray-700 hover:bg-gray-600">
                                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium max-w-[60ch] truncate" title="{{ e($q->content) }}">{{ Str::limit(str_replace(["\r\n","\n"], ' ', $q->content), 35) }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30 text-xs">{{ $q->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
                                    </td>
                                    <td class="px-4 py-3 align-top text-sm text-gray-300">{{ $q->choices_count }}</td>
                                    <td class="px-4 py-3 align-top text-sm text-gray-300">{{ $q->answers_count }}</td>
                                    <td class="px-4 py-3 align-top text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('docent.questions.results', $q) }}" class="text-xs px-3 py-1.5 rounded bg-sky-600 hover:bg-sky-700 inline-block">Bekijk antwoorden</a>
                                        <form method="POST" action="{{ route('docent.questions.destroy', $q) }}" class="inline" onsubmit="return confirm('Weet je zeker dat je deze vraag wil verwijderen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 inline-block">Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak class="bg-gray-900/40">
                                    <td colspan="6" class="px-6 pb-6 pt-2">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            <div>
                                                <div class="text-sm text-gray-400 mb-1">Vraag</div>
                                                <div class="p-3 rounded border border-gray-700 bg-gray-800/60">{!! nl2br(e($q->content)) !!}</div>
                                                @if($q->type==='multiple_choice' && $q->choices->isNotEmpty())
                                                    <div class="mt-4">
                                                        <div class="text-sm text-gray-400 mb-2">Opties</div>
                                                        <ul class="text-sm text-gray-300 list-disc pl-5">
                                                            @foreach($q->choices as $ch)
                                                                <li>
                                                                    <span class="text-gray-400">{{ $ch->label }}.</span> {{ $ch->text }}
                                                                    @if($ch->is_correct)
                                                                        <span class="ml-2 text-emerald-400 text-xs">(juist)</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <form method="POST" action="{{ route('docent.questions.setCorrect', $q) }}" class="mt-3">
                                                        @csrf
                                                        <div class="text-sm mb-2">Juiste antwoord instellen:</div>
                                                        <div class="flex flex-wrap gap-3">
                                                            @foreach($q->choices as $ch)
                                                                <label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
                                                                    <input type="radio" name="choice_id" value="{{ $ch->id }}" class="form-radio text-emerald-500 bg-gray-800 border-gray-600" @checked($ch->is_correct)>
                                                                    <span class="ml-2"><span class="text-gray-400">{{ $ch->label }}.</span> {{ $ch->text }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        <button class="mt-3 px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-sm">Opslaan</button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div>
                                                <form method="POST" action="{{ route('docent.questions.activate', $q) }}" @click.stop class="">
                                                    @csrf
                                                    <div class="text-sm mb-2">Activeer voor klassen:</div>
                                                    @php($classesList = $classes instanceof \Illuminate\Support\Collection ? $classes : (is_array($classes) ? collect($classes) : collect()))
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($classesList as $class)
                                                            @if(is_object($class))
                                                            <label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
                                                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="form-checkbox text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded" @checked(optional($class->activeQuestion)->id === $q->id)>
                                                                <span class="ml-2">{{ $class->name }}</span>
                                                            </label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <button class="mt-3 px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-sm">Activeer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            @endforeach
                        </table>
                    </div>
                @endif
            </div>

            <!-- Classes and active question overview -->
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 border border-gray-700">
                <h3 class="text-2xl font-semibold mb-4">Klassen overzicht</h3>
                <ul class="divide-y divide-gray-700/70">
                    @php($classesList = $classes instanceof \Illuminate\Support\Collection ? $classes : (is_array($classes) ? collect($classes) : collect()))
                        @foreach($classesList as $class)
                            @if(is_object($class))
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $class->name }}</div>
                                    <div class="text-xs text-gray-400">Actieve vraag: {{ optional($class->activeQuestion)->id ? Str::limit($class->activeQuestion->content, 60) : '—' }}</div>
                                </div>
                                <form method="POST" action="{{ route('docent.classes.clear', $class) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-sm">Wis actief</button>
                                </form>
                            </li>
                            @endif
                        @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
