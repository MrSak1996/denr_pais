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


    // ✅ Return BOTH via Inertia
    return Inertia::render('monitoring/form/insert', [
        'protectedAreas' => $protectedAreas,
    ]);
    
}
}
