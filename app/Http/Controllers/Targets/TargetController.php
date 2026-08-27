<?php

namespace App\Http\Controllers\Targets;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TargetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('targets/index', [
            'data' => $user,
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'       => ['required', 'string', 'max:255'],
            'category_other' => ['nullable', 'string', 'max:255'],
            'program'        => ['required', 'string', 'max:255'],
            'measure_text'   => ['required', 'string'],
            'first_sem'      => ['nullable', 'string', 'max:50'],
            'second_sem'     => ['nullable', 'string', 'max:50'],
            'total'          => ['nullable', 'numeric', 'max:50'],
            'remarks'        => ['nullable', 'string'],
        ]);
 
        // If "Other" was picked, use the free-text value as the category.
        $category = $validated['category'] === '__other__'
            ? ($validated['category_other'] ?? 'UNCATEGORIZED')
            : $validated['category'];
 
        DB::table('targets')->insert([
            'user_id'      => $request->user()?->id,
            'category'     => $category,
            'program'      => $validated['program'],
            'measure_text' => $validated['measure_text'],
            'first_sem'    => $validated['first_sem'] ?? null,
            'second_sem'   => $validated['second_sem'] ?? null,
            'total'        => $validated['total'] ?? null,
            'remarks'      => $validated['remarks'] ?? null,
        ]);
 
        // Picked up by the Inertia shared "flash" prop and shown as a toast
        // on the frontend (see HandleInertiaRequests middleware below).
        return back()->with('success', 'Target / measure saved successfully.');
    }
}