<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity\ActivityModel as Activity;

class ActivityController extends Controller
{
    public function index()
    {
        return Activity::all()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'start' => $activity->start_date,
                'end' => $activity->end_date,
                'color' => $activity->color,
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'color' => 'nullable|string'
        ]);

        Activity::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start'],
            'end_date' => $validated['end'] ?? null,
            'color' => $validated['color'] ?? '#2563eb'
        ]);

        return response()->json([
            'message' => 'Activity saved.'
        ]);
    }
}
