<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Services\CycleProgress;
use Illuminate\Http\Request;

class CycleReleaseController extends Controller
{
    public function store(Request $request, Programme $programme, CycleProgress $progress)
    {
        $progress->release($request->user(), $programme);

        return back()->with('status', 'Cycle 2 is open for students who have completed Cycle 1.');
    }
}
