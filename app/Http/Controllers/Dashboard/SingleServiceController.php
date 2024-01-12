<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\SingleServiceRepositoryInterface;
use Illuminate\Http\Request;

class SingleServiceController extends Controller
{
    private $SingleService;
    public function __construct(SingleServiceRepositoryInterface $SingleService)
    {
        $this->SingleService = $SingleService;
    }
    public function index()
    {
        return $this->SingleService->index();
    }
    public function store(Request $request)
    {
        return $this->SingleService->store($request);
    }
    public function show(string $id)
    {
        //
    }
    public function edit(string $id)
    {
        //
    }
    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        //
    }
}
