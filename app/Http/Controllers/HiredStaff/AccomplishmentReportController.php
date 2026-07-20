<?php

namespace App\Http\Controllers\HiredStaff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HiredStaff\AccomplishmentReport;
use App\Models\HiredStaff\StaffInfo;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AccomplishmentReportController extends Controller
{

    public function index()
    {
        $data = AccomplishmentReport::with([
            'protectedAreaStaff.protectedArea'
        ])->get();

        $protectedAreas = DB::table('tbl_protected_area')
            ->select(
                'id',
                'pa_name',
                'pa_code',
                'created_at'
            )
            ->orderBy('pa_name', 'asc')
            ->get();

        return Inertia::render('hired_staff/index', [
            'data' => $data,
            'protectedAreas' => $protectedAreas,
        ]);
    }

    public function edit($id)
    {
        $accomplishmentReport = AccomplishmentReport::with([
            'protectedAreaStaff.protectedArea'
        ])->findOrFail($id);

        return Inertia::render('hired_staff/accomplishment/edit', [
            'accomplishmentReport' => $accomplishmentReport,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer'],
            'accomplishments' => ['required', 'array'],
            'accomplishments.*' => ['required', 'string'],
        ]);

        $accomplishmentReport = AccomplishmentReport::findOrFail($id);

        $accomplishmentReport->month = $validated['month'];
        $accomplishmentReport->accomplishments = $validated['accomplishments'];

        $accomplishmentReport->save();

        return redirect()
            ->back()
            ->with('success', 'Accomplishment report updated successfully.');
    }

    public function insertPAStaff(Request $request)
    {
        $validated = $request->validate([
            'protected_area_id' => 'required|exists:tbl_protected_area,id',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
        ]);

        StaffInfo::create([
            'pa_id'    => $validated['protected_area_id'],
            'name'     => $validated['name'],
            'position' => $validated['position'],
        ]);

        return back()->with('success', 'Protected Area Staff added successfully.');
    }
}
