<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
        ]);
        $class = new ClassModel();
        $class->name = $request->class_name;
        $class->save();
        return redirect()->back();
    }
}
