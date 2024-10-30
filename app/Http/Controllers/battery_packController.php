<?php

namespace App\Http\Controllers;

use App\Models\BatteryPack;
use Illuminate\Http\Request;

class battery_packController extends Controller
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
    public function show(BatteryPack $batteryPack)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BatteryPack $batteryPack)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BatteryPack $batteryPack)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BatteryPack $batteryPack)
    {
        //
    }
}
