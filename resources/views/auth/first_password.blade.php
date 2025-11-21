<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Stel een nieuw wachtwoord in om verder te gaan.
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.first.update') }}">
        @csrf

        <div class="mt-4">
            <x-input-label for="password" :value="__('Nieuw wachtwoord')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Bevestig wachtwoord')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Opslaan') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
