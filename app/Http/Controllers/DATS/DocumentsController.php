<?php

namespace App\Http\Controllers\DATS;

use App\Http\Controllers\Controller;
use App\Services\DATSService;
use Illuminate\Http\JsonResponse;

class DocumentsController extends Controller
{
    public function index(DATSService $datsService): JsonResponse
    {
        try {

            $data = $datsService->getDocuments();

            return response()->json([
                'success' => true,
                'total_records' => $data->count(),
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}