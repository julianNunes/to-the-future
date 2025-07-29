<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $data = ['title' => 'Hello World', 'items' => [1, 2, 3, 4, 5]];

        return Inertia::render('TestPage', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string', 'email' => 'required|email']);

        return redirect()->back()->with('success', 'Created successfully');
    }
}
