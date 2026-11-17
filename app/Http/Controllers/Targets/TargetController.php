<?php

namespace App\Http\Controllers\Targets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{TargetCategory, TargetProgram, TargetMeasures, TargetReport, TargetMeasureValue};


class TargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $values = TargetMeasureValue::with([
            'measure.program.category',
            'report',
        ])
            ->whereHas('report', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('fiscal_year', now()->year);
            })
            ->get();

        $categories = $values
            ->groupBy(function ($value) {
                return $value->measure->program->category->id;
            })
            ->map(function ($categoryValues) {

                $category = $categoryValues->first()
                    ->measure
                    ->program
                    ->category;

                return [
                    'label' => $category->name,

                    'rows' => $categoryValues
                        ->groupBy(function ($value) {
                            return $value->measure->program->id;
                        })
                        ->map(function ($programValues) {

                            $program = $programValues->first()
                                ->measure
                                ->program;

                            return [
                                'program' => $program->name,

                                'measures' => $programValues
                                    ->map(function ($value) {
                                        return [
                                            'text' => $value->measure->description,
                                            'firstSem' => $value->first_sem,
                                            'secondSem' => $value->second_sem,
                                            'total' => $value->total_target,
                                            'remarks' => $value->remarks,
                                        ];
                                    })
                                    ->values()
                                    ->toArray(),
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('targets/index', [
            'data' => $user,
            'categories' => $categories,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'       => 'required|string',
            'protected_area' => 'required|integer|exists:tbl_protected_area,id',
            'category_other' => 'nullable|string',
            'program'        => 'required|string',
            'measure_text'   => 'required|string',
            'first_sem'      => 'nullable|string',
            'second_sem'     => 'nullable|string',
            'total'          => 'nullable|numeric',
            'remarks'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $categoryName = $validated['category'] === '__other__'
                ? $validated['category_other']
                : $validated['category'];

            $category = TargetCategory::firstOrCreate(
                ['name' => $categoryName],
                ['order' => (TargetCategory::max('order') ?? 0) + 1]
            );

            $program = TargetProgram::firstOrCreate(
                ['target_category_id' => $category->id, 'name' => $validated['program']],
                ['order' => (TargetProgram::where('target_category_id', $category->id)->max('order') ?? 0) + 1]
            );

            $measure = TargetMeasures::firstOrCreate(
                ['target_program_id' => $program->id, 'description' => $validated['measure_text']],
                ['order' => (TargetMeasures::where('target_program_id', $program->id)->max('order') ?? 0) + 1]
            );

            // one report per user/protected area/fiscal year, created as 'draft'
            $report = TargetReport::firstOrCreate(
                [
                    'user_id'            => $request->user()->id,
                    'protected_area_id'  => $validated['protected_area'],
                    'fiscal_year'        => now()->year,
                ],
                ['status' => 'draft']
            );

            TargetMeasureValue::updateOrCreate(
                [
                    'target_report_id'  => $report->id,
                    'target_measure_id' => $measure->id,
                ],
                [
                    'first_sem'    => $validated['first_sem'] ?? null,
                    'second_sem'   => $validated['second_sem'] ?? null,
                    'total_target' => $validated['total'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                ]
            );
        });

        return back()->with('success', 'Target/Measure saved successfully.');
    }
    // public function store(Request $request): RedirectResponse
    // {
    //     $validated = $request->validate([
    //         'category'       => ['required', 'string', 'max:255'],
    //         'category_other' => ['nullable', 'string', 'max:255'],
    //         'program'        => ['required', 'string', 'max:255'],
    //         'measure_text'   => ['required', 'string'],
    //         'first_sem'      => ['nullable', 'string', 'max:50'],
    //         'second_sem'     => ['nullable', 'string', 'max:50'],
    //         'total'          => ['nullable', 'numeric', 'max:50'],
    //         'remarks'        => ['nullable', 'string'],
    //     ]);

    //     // If "Other" was picked, use the free-text value as the category.
    //     $category = $validated['category'] === '__other__'
    //         ? ($validated['category_other'] ?? 'UNCATEGORIZED')
    //         : $validated['category'];

    //     DB::table('targets')->insert([
    //         'user_id'      => $request->user()?->id,
    //         'category'     => $category,
    //         'program'      => $validated['program'],
    //         'measure_text' => $validated['measure_text'],
    //         'first_sem'    => $validated['first_sem'] ?? null,
    //         'second_sem'   => $validated['second_sem'] ?? null,
    //         'total'        => $validated['total'] ?? null,
    //         'remarks'      => $validated['remarks'] ?? null,
    //     ]);

    //     // Picked up by the Inertia shared "flash" prop and shown as a toast
    //     // on the frontend (see HandleInertiaRequests middleware below).
    //     return back()->with('success', 'Target / measure saved successfully.');
    // }
}
