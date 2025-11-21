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
        // Eager load classes to avoid N+1 in grouping by class in the admin view
        $users = User::with('classes')->get();
        return view('admin_dashboard', [
            'users' => $users,
            'classes' => ClassModel::all(),
        ]);
    }


    public function store(Request $request)
    {

        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,docent,student',
            'password' => 'required|string|min:6',
            'class_id' => 'nullable|array',
            'class_id.*' => 'exists:classes,id',
        ];
        // Distinguish identifier: students use numeric login id, others use email
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
        $user->password = Hash::make($request->password ?? 'password');
        $user->save();

        // Attach to classes if student or docent, avoid duplicates
        if (in_array($user->role, ['student', 'docent']) && $request->filled('class_id')) {
            $user->classes()->sync($request->class_id);
        }

        return redirect()->back();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('edit_user', [
            'user' => $user,
            'classes' => ClassModel::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Build validation rules
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
        }
        $user->save();

        // Sync class connections
        if (in_array($user->role, ['student', 'docent'])) {
            $user->classes()->sync($request->class_id ?? []); // empty array detaches all if none selected
        } else {
            // Admin should not have class links
            $user->classes()->detach();
        }

        return redirect()->route('admin_dashboard')->with('status', 'Gebruiker bijgewerkt');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back();
    }
}
