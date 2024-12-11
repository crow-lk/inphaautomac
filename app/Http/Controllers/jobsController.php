<?php

namespace App\Http\Controllers;

use App\Models\Inpha_Job;
use App\Models\Job;
use Illuminate\Http\Request;

class jobsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return 'This your index page';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Inpha_Job $inpha_job)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inpha_Job $inpha_job)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inpha_Job $inpha_job)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inpha_Job $inpha_job)
    {
        //
    }
}
