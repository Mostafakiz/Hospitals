<?php
namespace App\Interfaces\Services;

interface SingleServiceRepositoryInterface
{
    //Get SingleService
    public function index();
    //Add SingleService
    public function store($request);
}