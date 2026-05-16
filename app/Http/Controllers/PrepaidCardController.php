<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrepaidCard\StorePrepaidCardRequest;
use App\Http\Requests\PrepaidCard\UpdatePrepaidCardRequest;
use App\Services\Interfaces\PrepaidCardServiceInterface;
use Inertia\Inertia;

class PrepaidCardController extends Controller
{
    public function __construct(private PrepaidCardServiceInterface $prepaidCardService)
    {
    }

    /**
     * Returns data to Prepiad Card Management
     */
    public function index()
    {
        $data = $this->prepaidCardService->index();
        return Inertia::render('PrepaidCard/Index', $data);
    }

    /**
     * Create new Prepaid Card
     */
    public function store(StorePrepaidCardRequest $request)
    {
        $data = $request->validated();

        $this->prepaidCardService->create(
            $data['name'],
            $data['digits'],
            $data['is_active']
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Prepaid Card
     * @param integer $id
     */
    public function update(UpdatePrepaidCardRequest $request, int $id)
    {
        $data = $request->validated();

        $this->prepaidCardService->update(
            $id,
            $data['name'],
            $data['digits'],
            $data['is_active']
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Prepaid Card
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->prepaidCardService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
