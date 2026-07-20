<?php

namespace App\Http\Controllers\ProtectedArea;

use App\Services\PAService;
use App\Http\Controllers\Controller;
use App\Models\ProtectedArea\LawinModel;
use App\Models\ProtectedArea\PatrolPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LawinPatrolController extends Controller
{
    public function index(PAService $paService)
    {
        $protectedAreas = $paService->all();

        return Inertia::render('lawin_monitoring/index', [
            'protectedAreas' => $protectedAreas,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'protected_area_id' => 'required',
            'area' => 'required',
            // 'target_patrol_sector' => 'required',
            'conservation_targets' => 'required',
            'target_patrol_route_distance' => 'required',
            'target_patrol_sector_name' => 'required',
            'target_patrol_sector_area' => 'required',
            'target_patrol_sector_location' => 'required',
            'target_patrol_period' => 'required',
            'patrol_team_members' => 'required',
            'data_manager' => 'required',
            'number_of_patrollers' => 'required|integer',
        ]);

        LawinModel::create($validated);

        return back()->with('success', 'Record saved successfully.');
    }

    public function getLawinData()
    {
       $data = PatrolPlan::with('protectedArea')->get();
       return response()->json($data);
    }
}
