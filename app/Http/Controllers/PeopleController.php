<?php

namespace App\Http\Controllers;

use App\Models\People;
use App\Services\Interfaces\PeopleServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeopleController extends Controller
{
    public function __construct(private PeopleServiceInterface $peopleService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return Inertia::render(
            'People/Index',
            $this->peopleService->index(
                $request->string('search')->toString() ?: null,
                $request->input('sort'),
                (int) $request->input('limit', 10),
            )
        );
    }

    public function create()
    {
        return Inertia::render('People/Create');
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['required'],
            'gender' => ['required'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable'],
            'address' => ['nullable'],
        ]);

        $people = $this->peopleService->create($data);
        $message = sprintf('Successfully created %s', $people->name);

    return to_route('people.index')->with('success', $message);
    }

    public function edit(People $person)
    {
        return Inertia::render('People/Edit', [
            'person' => $this->peopleService->show($person->id),
        ]);
    }

    public function update(People $person, Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['required'],
            'gender' => ['required'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable'],
            'address' => ['nullable'],
        ]);

        $person = $this->peopleService->update($person->id, $data);
        $message = sprintf('Successfully updated %s', $person->name);

        return to_route('people.index')->with('success', $message);
    }

    public function destroy(People $person)
    {
        $person = $this->peopleService->show($person->id);
        $this->peopleService->delete($person->id);
        $message = sprintf('Successfully deleted %s', $person->name);

        return to_route('people.index')->with('success', $message);
    }
}
