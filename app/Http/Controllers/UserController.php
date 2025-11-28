<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function admin_index()
    {
        // Admin-overzicht: alle gebruikers en hun klassen.
        // Laad klassen eager om N+1 queries te voorkomen in de admin-view
        $users = User::with('classes')->get();
        return view('admin_dashboard', [
            'users' => $users,
            'classes' => ClassModel::all(),
        ]);
    }


    public function store(Request $request)
    {
        // Nieuwe gebruiker aanmaken.
        // Studenten loggen in met een numerieke ID (we bewaren die in het e-mailveld),
        // anderen gebruiken een normale e-mail. Standaard wachtwoord is 'Welkom123!' als er niks is opgegeven.

        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,docent,student',
            'password' => 'nullable|string|min:6',
            'class_id' => 'nullable|array',
            'class_id.*' => 'exists:classes,id',
        ];
        // Verschil in identifier: studenten gebruiken numeriek login-id, anderen e-mail
        if ($request->role === 'student') {
            $rules['login_id'] = 'required|string|regex:/^[0-9]{4,20}$/|unique:users,email';
            $rules['class_id'] = 'required|array|min:1';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
        }
        $request->validate($rules);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->role === 'student' ? $request->login_id : $request->email;
        $user->role = $request->role;
        $providedPassword = $request->password;
        $user->password = Hash::make($providedPassword ?: 'Welkom123!');
        $user->must_change_password = empty($providedPassword);
        $user->save();

        // Attach to classes if student or docent, avoid duplicates
        // Als ze bij klassen horen (student/docent), koppelen we ze meteen.
        if (in_array($user->role, ['student', 'docent']) && $request->filled('class_id')) {
            $user->classes()->sync($request->class_id);
        }

        return redirect()->back();
    }

    public function edit($id)
    {
        // Toon het bewerkformulier voor één gebruiker, plus alle klassen om uit te kiezen.
        $user = User::findOrFail($id);
        return view('edit_user', [
            'user' => $user,
            'classes' => ClassModel::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        // Gebruiker bijwerken, inclusief klas-koppelingen.
        // Als je een nieuw wachtwoord zet, dan hoeft het niet bij de volgende login gewijzigd te worden.
        $user = User::findOrFail($id);

        // Bouw validatieregels
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,docent,student',
            'password' => 'nullable|string|min:6',
            'class_id' => 'nullable|array',
            'class_id.*' => 'exists:classes,id'
        ];
        if ($request->role === 'student') {
            $rules['login_id'] = 'required|string|regex:/^[0-9]{4,20}$/|unique:users,email,' . $user->id;
            $rules['class_id'] = 'required|array|min:1';
        } else {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }
        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['role'] === 'student' ? $validated['login_id'] : $validated['email'];
        $user->role = $validated['role'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->must_change_password = false;
        }
        $user->save();

        // Synchroniseer klas-koppelingen
        // Studenten/docenten houden geselecteerde klassen; admins horen geen klassen te hebben.
        if (in_array($user->role, ['student', 'docent'])) {
            $user->classes()->sync($request->class_id ?? []); // empty array detaches all if none selected
        } else {
            // Admins mogen geen klas-koppelingen hebben
            $user->classes()->detach();
        }

        return redirect()->route('admin_dashboard')->with('status', 'Gebruiker bijgewerkt');
    }

    public function destroy($id)
    {
        // Verwijder een gebruiker. Klaar.
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back();
    }

    public function resetPassword($id)
    {
        // Admins en docenten kunnen wachtwoorden resetten.
        // Docenten mogen alleen student-wachtwoorden resetten; admins mogen bij iedereen.
        $current = auth()->user();
        if (!$current || !in_array($current->role, ['admin','docent'])) {
            abort(403);
        }
        $user = User::findOrFail($id);
        if ($user->role !== 'student' && $current->role !== 'admin') {
            // Alleen admins mogen niet-student wachtwoorden resetten
            abort(403);
        }
        $user->password = Hash::make('Welkom123!');
        $user->must_change_password = true;
        $user->save();
        return redirect()->back()->with('status', 'Wachtwoord gereset naar standaard en wijziging vereist bij volgende login.');
    }
}
