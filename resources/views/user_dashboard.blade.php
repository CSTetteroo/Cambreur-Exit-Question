<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl leading-tight text-gray-100">Welkom</h2>
	</x-slot>

	<div class="py-8 bg-gray-900 text-gray-100 min-h-screen">
		<div class="max-w-5xl mx-auto px-4 space-y-8">
			<section class="bg-gray-800/80 border border-gray-700 rounded-lg p-6">
				<h3 class="text-2xl font-semibold mb-2">Hallo, {{ $user->name }}</h3>
				<p class="text-gray-300 text-sm mb-1">Je bent ingelogd als <span class="font-medium">{{ ucfirst($user->role) }}</span>.</p>
				<p class="text-gray-300 text-sm">E-mail: {{ $user->email }}</p>
			</section>

			@if(in_array($user->role, ['admin','docent']))
			<section class="bg-gray-800/80 border border-gray-700 rounded-lg p-6">
				<h3 class="text-xl font-semibold mb-3">Alle klassen</h3>
                @if(($allClasses ?? collect())->isEmpty())
					<p class="text-gray-400">Geen klassen gevonden.</p>
				@else
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
							@foreach($allClasses as $class)
							<div class="p-4 bg-gray-800/60 rounded border border-gray-700 flex flex-col justify-between h-full">
								<div>
									<div class="font-semibold text-gray-100 mb-1"><a href="{{ route('classes.show', $class) }}" class="hover:underline">{{ $class->name }}</a></div>
									<div class="text-xs text-gray-400">Actieve vraag:</div>
									<div class="text-sm text-gray-200 mt-1 break-words">{{ optional($class->activeQuestion)->id ? Str::limit(optional($class->activeQuestion)->content, 120) : '(N/A)' }}</div>
								</div>
								<div class="mt-4 flex justify-end">
									<a href="{{ route('classes.show', $class) }}" class="px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-sm">Bekijk klas</a>
								</div>
							</div>
						@endforeach
					</div>
				@endif
			</section>
			@endif

			<section class="bg-gray-800/80 border border-gray-700 rounded-lg p-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-xl font-semibold">Mijn vragen</h3>
					<div class="flex items-center gap-3">
						@if($user->role==='docent')
							<a href="{{ route('docent.questions.index') }}" class="text-xs px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700">Vragen beheren</a>
						@endif
						@if($user->role==='student')
							<span class="text-xs text-gray-400">Klassen: {{ ($myClasses ?? collect())->count() }}</span>
						@else
							<span class="text-xs text-gray-400">Totaal: {{ $questions->count() }}</span>
						@endif
					</div>
				</div>

				@if($user->role==='student')
					@php $classesList = ($myClasses ?? collect()); @endphp
					@if($classesList->isEmpty())
						<p class="text-gray-400 text-sm">Er zijn momenteel geen vragen voor jouw klassen.</p>
					@else
						<ul class="divide-y divide-gray-700/70">
						@foreach($classesList as $class)
							<li class="py-4 flex flex-col gap-3 hover:bg-gray-700/40 px-2 rounded transition">
								<div class="flex items-center justify-between">
									<div class="font-semibold">Klas: {{ $class->name }}</div>
									@if($class->activeQuestion)
										<span class="text-xs text-gray-400">Vraag #{{ $class->activeQuestion->id }}</span>
									@endif
								</div>
								@if(!$class->activeQuestion)
									<div class="text-gray-400 text-sm">Geen actieve vraag.</div>
								@else
								@php $question = $class->activeQuestion; $already = isset($answeredIds) && in_array($question->id, $answeredIds ?? []); @endphp
								<div>
									<p class="text-gray-100 whitespace-pre-line break-words">{{ Str::limit($question->content, 280) }}</p>
									<div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
										<span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
										<span>Docent: {{ optional($question->creator)->name ?? 'Onbekend' }}</span>
										<span>Geplaatst: {{ $question->created_at->diffForHumans() }}</span>
									</div>
								</div>
								@if($already)
									<div class="px-3 py-2 rounded border border-emerald-700 bg-emerald-800/40 text-emerald-100 text-sm">Jouw antwoord is opgeslagen!</div>
								@else
									<form method="POST" action="{{ route('answers.store') }}" class="space-y-2">
										@csrf
										<input type="hidden" name="question_id" value="{{ $question->id }}">
										@if($question->type==='multiple_choice')
											<div class="flex flex-wrap gap-3">
												@foreach($question->choices as $choice)
													<label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded break-words">
														<input type="radio" name="choice_id" value="{{ $choice->id }}" class="form-radio text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded" required>
														<span class="ml-2"><span class="text-gray-400">{{ $choice->label }}.</span> {{ $choice->text }}</span>
													</label>
												@endforeach
											</div>
										@else
											<textarea name="answer_text" class="w-full min-h-24 px-3 py-2 rounded bg-gray-700 border border-gray-600" placeholder="Typ je antwoord..."></textarea>
										@endif
										<button class="px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-sm">Verstuur antwoord</button>
									</form>
								@endif
								@endif
							</li>
						@endforeach
						</ul>
					@endif
				@else
					@if($questions->isEmpty())
						<p class="text-gray-400 text-sm">Er zijn momenteel geen vragen beschikbaar.</p>
					@else
						<ul class="divide-y divide-gray-700/70">
							@foreach($questions as $question)
								<li class="py-4 flex flex-col gap-3 hover:bg-gray-700/40 px-2 rounded transition">
									<div>
										<p class="text-gray-100 whitespace-pre-line break-words">{{ Str::limit($question->content, 280) }}</p>
										<div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
											<span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
											<span>Docent: {{ optional($question->creator)->name ?? 'Onbekend' }}</span>
											<span>Geplaatst: {{ $question->created_at->diffForHumans() }}</span>
										</div>
									</div>
								</li>
							@endforeach
						</ul>
					@endif
				@endif
			</section>

			<section class="text-center text-xs text-gray-500 pt-4">
				<p>Laatste update: {{ now()->format('d-m-Y H:i') }}</p>
			</section>
		</div>
	</div>
</x-app-layout>
