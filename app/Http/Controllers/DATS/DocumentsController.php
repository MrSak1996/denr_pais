<?php

namespace App\Http\Controllers\DATS;

use App\Http\Controllers\Controller;
use App\Models\DATAS\Document;
use App\Services\DATSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\DATS\Exception;
use Illuminate\Support\Facades\Cache;

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
    public function exportToSheet(DATSService $dats)
    {
        $rows = Document::query()
            ->get()
            ->map(fn($doc) => [
                $doc->id,
                $doc->name,
                $doc->order,
                $doc->created_at->format('Y-m-d'),
            ])
            ->toArray();

        $cells = $dats->appendRows($rows);

        return response()->json([
            'message' => 'Saved to Google Sheet',
            'cells'   => $cells,
        ]);
    }
    public function syncFromRdats(DATSService $dats, Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        // Only one sync at a time (lock auto-expires after 2 minutes)
        $lock = Cache::lock('dats-sync-sheet6', 120);

        if (!$lock->get()) {
            return response()->json(['message' => 'A sync is already running. Please wait.'], 409);
        }

        try {
            $count = $dats->syncToColumns(
                [
                    'A' => 'docid',
                    'B' => 'subject',
                    'C' => 'date_received',
                    'D' => 'originating_office',
                    'E' => 'sender',
                    'F' => 'action_required',
                ],
                startRow: 30,
                dateFrom: $request->query('date_from'),
                dateTo: $request->query('date_to'),
            );

            return response()->json([
                'message' => "{$count} rows saved to Google Sheet",
                'count'   => $count,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        } finally {
            $lock->release();
        }
    }

    public function previewFromRdats(DATSService $dats)
    {
        try {
            $rows = $dats->previewRows();
            return response()->json(['data' => $rows, 'count' => count($rows)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
