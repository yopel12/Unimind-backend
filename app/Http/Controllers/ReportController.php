<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => 'required|integer',
            'reporter_user_id' => 'required|integer',
            'reason' => 'required|string|max:1000',
        ]);

        $report = Report::create($validated);

        return response()->json([
            'message' => 'Report submitted successfully',
            'report' => $report,
        ], 201);
    }
}
