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
					<span class="text-xs text-gray-400">Totaal: {{ $questions->count() }}</span>
				</div>
				@if($questions->isEmpty())
					<p class="text-gray-400 text-sm">Er zijn momenteel geen vragen beschikbaar.</p>
				@else
					<ul class="divide-y divide-gray-700/70">
						@foreach($questions as $question)
							<li class="py-4 flex flex-col gap-1 hover:bg-gray-700/40 px-2 rounded transition">
								<p class="text-gray-100 whitespace-pre-line">{{ Str::limit($question->content, 180) }}</p>
								<div class="flex items-center gap-3 text-xs text-gray-400">
									<span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 border border-indigo-600/30">{{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}</span>
									<span>Docent: {{ optional($question->creator)->name ?? 'Onbekend' }}</span>
									<span>Geplaatst: {{ $question->created_at->diffForHumans() }}</span>
								</div>
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
