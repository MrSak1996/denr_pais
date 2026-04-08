<?php

namespace App\Http\Controllers\ProtectedArea;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProtectedArea\PambResolution;
use Inertia\Inertia;
use Carbon\Carbon;


class ResolutionClearanceController extends Controller
{

    /**
     * Display the monitoring table
     */
    public function index()
    {
        $resolutions = PambResolution::latest()->get();

        return Inertia::render('monitoring/index', [
            'resolutions' => $resolutions
        ]);
    }

    /**
     * Store new resolution
     */


   public function store(Request $request)
{
    $request->validate([
        'resolutions' => 'required|array|min:1',
        'resolutions.*.resolution_no' => 'required|string|max:100',
        'status' => 'required|string',
    ]);

    foreach ($request->resolutions as $row) {

        PambResolution::create([
            'protected_area_id' => $request->protected_area_id,

            // 👇 FROM ARRAY
            'resolution_no' => $row['resolution_no'],
            'type_of_meeting' => $row['type_of_meeting'] ?? null,
            'resolution_title' => $row['resolution_title'] ?? null,
            'date_of_meeting' => $row['date_of_meeting'] ?? null,

            // 👇 FROM MAIN FORM
            'focal_person' => $request->focal_person,
            'alternate_focal' => $request->alternate_focal,
            'approved_pamb_clearance_no' => $request->approved_pamb_clearance_no,
            'status' => $request->status,

            // 👇 TIMELINE
        
            'date_of_meeting' => $request->date_of_meeting,
            'date_received_by_cdd' => $request->date_received_cdd,
            'date_received_by_focal' => $request->date_received_focal,
            'date_submitted_released_by_focal' => $request->date_released_focal,
            'date_received_by_oardts' => $request->date_received_by_oardts,
            'date_approved_by_pamb_chair' => $request->date_approved_pamb,
            'date_emailed_bmb' => $request->date_emailed_bmb,
            'date_submitted_to_bmb_hardcopy' => $request->date_submitted_bmb,
        ]);
    }

    return redirect()->route('monitoring.index')
        ->with('success', 'Resolutions created successfully.');
}

    /**
     * Show single resolution
     */
    public function show($id)
    {
        $resolution = PambResolution::findOrFail($id);

        return response()->json($resolution);
    }

    /**
     * Update resolution
     */
    public function update(Request $request, $id)
    {
        $resolution = PambResolution::findOrFail($id);

        $resolution->update($request->all());

        return redirect()->back()->with('success', 'Resolution updated successfully.');
    }

    /**
     * Delete resolution
     */
    public function destroy($id)
    {
        $resolution = PambResolution::findOrFail($id);
        $resolution->delete();

        return redirect()->back()->with('success', 'Resolution deleted successfully.');
    }
}
