<?php

namespace App\Http\Controllers\ProtectedArea;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\DATSService;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $query = DB::table('pamb_resolutions as r')
            ->select(
                DB::raw('COUNT(*) as total'), // ✅ total records
                DB::raw('SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "Deferred" THEN 1 ELSE 0 END) as deferred'),
                DB::raw('SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved')
            );

        $data = $query->first(); // ✅ single object instead of array

        return response()->json($data);
    }
    public function monthlyMonitoring(Request $request)
    {
        $year = $request->year ?? date('Y');

        $data = DB::table('pamb_resolutions')
            ->leftJoin('tbl_protected_area as pa', 'pamb_resolutions.protected_area_id', '=', 'pa.id')
            ->select(
                // ✅ Use PA CODE (recommended)
                'pa.pa_code as pa',

                // ✅ CONCAT focal + alternate (NULL-safe)
                DB::raw("
                CONCAT(
                    pamb_resolutions.focal_person,
                    IF(
                        pamb_resolutions.alternate_focal IS NOT NULL 
                        AND pamb_resolutions.alternate_focal != '',
                        CONCAT(' / ', pamb_resolutions.alternate_focal),
                        ''
                    )
                ) as focal
            "),

                // ✅ MONTHLY COUNTS
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=1 THEN 1 ELSE 0 END) as jan'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=2 THEN 1 ELSE 0 END) as feb'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=3 THEN 1 ELSE 0 END) as mar'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=4 THEN 1 ELSE 0 END) as apr'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=5 THEN 1 ELSE 0 END) as may'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=6 THEN 1 ELSE 0 END) as jun'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=7 THEN 1 ELSE 0 END) as jul'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=8 THEN 1 ELSE 0 END) as aug'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=9 THEN 1 ELSE 0 END) as sep'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=10 THEN 1 ELSE 0 END) as oct'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=11 THEN 1 ELSE 0 END) as nov'),
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" and MONTH(date_approved_by_pamb_chair)=12 THEN 1 ELSE 0 END) as `dec`'),

                // ✅ SUMMARY
                DB::raw('SUM(CASE WHEN pamb_resolutions.status = "Approved" THEN 1 ELSE 0 END) as approved_resolutions'),
                DB::raw('COUNT(approved_pamb_clearance_no) as approved_clearances'),
                DB::raw('COUNT(*) as received')
            )

            ->groupBy(
                'pa.id',
                'pa.pa_code',
                'pamb_resolutions.focal_person',
                'pamb_resolutions.alternate_focal'
            )
            ->orderBy('pa.id', 'asc')
            ->get();

        return response()->json($data);
    }

    public function monthlySubmissionMonitoring(Request $request)
    {
        $year = $request->year ?? date('Y');

        $data = DB::table('tbl_accomplishment_reports as ar')
            ->leftJoin('tbl_pa_staff as ps', 'ar.staff_id', '=', 'ps.id')
            ->leftJoin('tbl_protected_area as pa', 'pa.id', '=', 'ps.pa_id')
            ->select(

                // STAFF NAME
                DB::raw("
                pa.pa_code
            "),

                // MONTHLY SUBMISSIONS
                DB::raw('SUM(CASE WHEN ar.month = 1 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as jan'),
                DB::raw('SUM(CASE WHEN ar.month = 2 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as feb'),
                DB::raw('SUM(CASE WHEN ar.month = 3 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as mar'),
                DB::raw('SUM(CASE WHEN ar.month = 4 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as apr'),
                DB::raw('SUM(CASE WHEN ar.month = 5 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as may'),
                DB::raw('SUM(CASE WHEN ar.month = 6 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as jun'),
                DB::raw('SUM(CASE WHEN ar.month = 7 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as jul'),
                DB::raw('SUM(CASE WHEN ar.month = 8 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as aug'),
                DB::raw('SUM(CASE WHEN ar.month = 9 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as sep'),
                DB::raw('SUM(CASE WHEN ar.month = 10 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as oct'),
                DB::raw('SUM(CASE WHEN ar.month = 11 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as nov'),
                DB::raw('SUM(CASE WHEN ar.month = 12 AND ar.is_submitted = 1 THEN 1 ELSE 0 END) as `dec`'),

                // TOTAL SUBMITTED
                DB::raw('SUM(CASE WHEN ar.is_submitted = 1 THEN 1 ELSE 0 END) as total_submitted'),

                // TOTAL REPORTS
                DB::raw('COUNT(ar.id) as total_reports')
            )

            // FILTER YEAR
            ->whereYear('ar.submitted_at', $year)

            ->groupBy(
                'pa.id',
                'pa.pa_code'
            )

            ->orderBy('pa.pa_code', 'asc')
            ->get();

        return response()->json($data);
    }


    public function pendingMinutes(DATSService $datsService)
    {
        $documents = $datsService->getDocuments();

        $startDate = Carbon::create(2026, 6, 1)->startOfDay();
        $today = Carbon::today();

        $pendingDocuments = $documents
            ->filter(function ($row) use ($startDate, $today) {

                $dateEncoded = trim($row['DATE ENCODED'] ?? '');

                if (!$dateEncoded) {
                    return false;
                }

                try {
                    $date = Carbon::createFromFormat('n/j/Y', $dateEncoded);
                } catch (\Exception $e) {
                    return false;
                }

                // Only include documents from May 1, 2026 until today
                if (!$date->betweenIncluded($startDate, $today)) {
                    return false;
                }

                $category = strtoupper($row['CATEGORY'] ?? '');

                $isTargetCategory =
                    str_contains($category, 'MINUTES') ||
                    str_contains($category, 'BMS') ||
                    str_contains($category, 'NIPAS') ||
                    str_contains($category, 'SAPA') ||
                    str_contains($category, 'HIRED STAFF') ||
                    str_contains($category, 'AWS') ||
                    str_contains($category, 'PA PATROLLING');

                $hasMov = strtoupper(trim($row["MOV'S"] ?? '')) === 'YES';

                $hasOutgoingCDD = trim($row["OUTGOING DATE\n(CDD)"] ?? '') !== '';

                return $isTargetCategory && $hasMov && !$hasOutgoingCDD;
            })
            ->values();

        $counts = [
            'minutes' => $pendingDocuments->filter(fn($row) => str_contains(strtoupper($row['CATEGORY']), 'MINUTES'))->count(),
            'bms'     => $pendingDocuments->filter(fn($row) => str_contains(strtoupper($row['CATEGORY']), 'BMS'))->count(),
            'nipas'   => $pendingDocuments->filter(fn($row) => str_contains(strtoupper($row['CATEGORY']), 'NIPAS'))->count(),
            'sapa'    => $pendingDocuments->filter(fn($row) => str_contains(strtoupper($row['CATEGORY']), 'SAPA'))->count(),
        ];

        return Inertia::render('monitoring/pending', [
            'documents' => $pendingDocuments,
            'total'     => $pendingDocuments->count(),
            'counts'    => $counts,
        ]);
    }

    public function completedMinutes(DATSService $datsService)
    {
        $documents = $datsService->getDocuments();

        $completedMinutes = $documents
            ->filter(function ($row) {

                $dateEncoded = $row['DATE ENCODED'] ?? '';

                if (!$dateEncoded) {
                    return false;
                }

                $parts = explode('/', $dateEncoded);

                if (count($parts) !== 3) {
                    return false;
                }

                $year = (int) $parts[2];

                if ($year !== 2026) {
                    return false;
                }

                $isMinutes =
                    str_contains(
                        strtoupper($row['CATEGORY'] ?? ''),
                        'MINUTES'
                    );

                $hasMov =
                    strtoupper(trim($row["MOV'S"] ?? '')) === 'YES';

                $hasOutgoingCDD =
                    trim($row["OUTGOING DATE\n(CDD)"] ?? '') !== '';

                return $isMinutes
                    && $hasMov
                    && $hasOutgoingCDD;
            })
            ->values();

        return Inertia::render(
            'monitoring/completed',
            [
                'documents' => $completedMinutes,
                'total' => $completedMinutes->count()
            ]
        );
    }
}
