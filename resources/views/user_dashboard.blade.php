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

			<section class="bg-gray-800/80 border border-gray-700 rounded-lg p-6">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-xl font-semibold">Beschikbare vragen</h3>
					<div class="flex items-center gap-3">
						@if($user->role==='docent')
							<a href="{{ route('docent.questions.index') }}" class="text-xs px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700">Vragen beheren</a>
						@endif
						<span class="text-xs text-gray-400">Totaal: {{ $questions->count() }}</span>
					</div>
				</div>
				@if($questions->isEmpty())
					<p class="text-gray-400 text-sm">Er zijn momenteel geen vragen beschikbaar.</p>
				@else
					<ul class="divide-y divide-gray-700/70">
						@foreach($questions as $question)
							<li class="py-4 flex flex-col gap-3 hover:bg-gray-700/40 px-2 rounded transition">
								<div>
									<p class="text-gray-100 whitespace-pre-line">{{ Str::limit($question->content, 280) }}</p>
									<div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
										<span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
										<span>Docent: {{ optional($question->creator)->name ?? 'Onbekend' }}</span>
										<span>Geplaatst: {{ $question->created_at->diffForHumans() }}</span>
									</div>
								</div>
								@if($user->role==='student')
									<form method="POST" action="{{ route('answers.store') }}" class="space-y-2">
										@csrf
										<input type="hidden" name="question_id" value="{{ $question->id }}">
										@if($question->type==='multiple_choice')
											<div class="flex flex-wrap gap-3">
												@foreach($question->choices as $choice)
													<label class="inline-flex items-center text-sm bg-gray-700/60 px-2 py-1 rounded">
														<input type="radio" name="choice_id" value="{{ $choice->id }}" class="form-radio text-indigo-500 focus:ring-indigo-600 bg-gray-800 border-gray-600 rounded">
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
							</li>
						@endforeach
					</ul>
				@endif
			</section>

			<section class="text-center text-xs text-gray-500 pt-4">
				<p>Laatste update: {{ now()->format('d-m-Y H:i') }}</p>
			</section>
		</div>
	</div>
</x-app-layout>
