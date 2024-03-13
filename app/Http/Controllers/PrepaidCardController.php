<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PrepaidCardServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrepaidCardController extends Controller
{
    public function __construct(private PrepaidCardServiceInterface $prepaidCardService)
    {
    }

    /**
     * Returns data to Credit Card Management
     */
    public function index()
    {
        $data = $this->prepaidCardService->index();
        return Inertia::render('PrepaidCard/Index', $data);
    }

    /**
     * Create new Credit Card
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->prepaidCardService->create(
            $request->name,
            $request->digits,
            $request->is_active == '1'
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Credit Card
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->prepaidCardService->update(
            $id,
            $request->name,
            $request->digits,
            $request->is_active == '1'
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Credit Card
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->prepaidCardService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
