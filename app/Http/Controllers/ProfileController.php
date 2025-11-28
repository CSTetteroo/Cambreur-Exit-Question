<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * dit heb ik echt last-minute eruit gehaald van breeze omdat ik het irritant vond dat iedereen z'n profiel kon aanpassen dus dit doet eigenlijk niets.
     */
    public function edit(Request $request): View
    {
        // Laat de profielpagina zien met de gegevens van de huidige gebruiker.
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Sla wijzigingen in je profiel op.
        // Als je je e-mail aanpast, halen we de verificatie weg zodat je opnieuw moet verifiëren.
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Verwijder je account (na wachtwoordcheck),
        // log je uit en ga terug naar de homepage.
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
