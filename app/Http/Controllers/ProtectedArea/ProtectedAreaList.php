<?php

namespace App\Http\Controllers\ProtectedArea;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProtectedArea\ProtectedAreaList as ProtectedAreaModel;
use Inertia\Inertia;

class ProtectedAreaList extends Controller
{
    public function index()
    {
        // ✅ Protected Areas (your existing code)
        $protectedAreas = DB::table('tbl_protected_area')
            ->select(
                'id',
                'pa_name',
                'pa_code',
                'created_at'
            )
            ->orderBy('pa_name', 'asc')
            ->get();
        return Inertia::render('monitoring/form/insert', [
            'protectedAreas' => $protectedAreas
        ]);
    }



    public function getPAStaffPerPA($pa_id)
    {
        $paStaff = DB::table('tbl_pa_staff')
            ->join('tbl_protected_area', 'tbl_pa_staff.pa_id', '=', 'tbl_protected_area.id')
            ->select(
                'tbl_pa_staff.id',
                'tbl_pa_staff.name',
                'tbl_pa_staff.position',
                'tbl_pa_staff.pa_id',
                'tbl_protected_area.pa_name'
            )
            ->where('tbl_pa_staff.pa_id', $pa_id)
            ->orderBy('tbl_pa_staff.name', 'asc')
            ->get();

        return response()->json($paStaff);
    }

    public function insert_pa_ar(Request $request)
    {
        $request->validate([
            'protected_area_id' => 'required|exists:tbl_protected_area,id',
            'pa_staff_id' => 'required|array|min:1',
            'pa_staff_id.*' => 'exists:tbl_pa_staff,id',
            'months' => 'required|array|min:1',
            'accomplishments' => 'required|array|min:1',
        ]);

        foreach ($request->pa_staff_id as $staffId) {

            foreach ($request->months as $month) {

                DB::table('tbl_accomplishment_reports')->insert([
                    'staff_id' => $staffId,
                    'month' => $month,
                    'accomplishments' => json_encode($request->accomplishments),
                    'is_submitted' => 1,
                    'submitted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Saved successfully');
    }
}
