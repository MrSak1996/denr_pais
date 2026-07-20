<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class PAService
{
    public function all()
    {
        return DB::table('tbl_protected_area')
            ->select('id', 'pa_name', 'pa_code', 'created_at')
            ->orderBy('pa_name')
            ->get();
    }
}
?>