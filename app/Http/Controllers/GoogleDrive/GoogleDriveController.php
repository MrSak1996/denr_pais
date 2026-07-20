<?php

namespace App\Http\Controllers\GoogleDrive;

use App\Jobs\UploadIECImageJob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\DB;
use App\Models\IECCEPA\IECCEPAReport;
use App\Models\IECCEPA\IECCEPAModel;

class GoogleDriveController extends Controller
{
    // public function upload_iec(Request $request)
    // {
    //     $request->validate([
    //         'protected_area_id' => ['required'],
    //         'accomplishments'   => ['required', 'array'],
    //         'quarter' => 'required|integer|between:1,4',
    //         'iec_images'        => ['nullable', 'array', 'max:15'],
    //         'iec_images.*'      => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
    //     ]);

    //     try {

    //         DB::beginTransaction();

    //         // Save accomplishment reports first
    //         foreach ($request->accomplishments as $i => $accomplishment) {

    //             $this->insert_iec_report(
    //                 $accomplishment,
    //                 $request->$validated[],
    //                 $request->details[$i] ?? null,
    //                 $request->protected_area_id
    //             );
    //         }

    //         DB::commit();

    //         $folderPath = '5. PAMBCS/2026/IEC_CEPA';

    //         // Queue every uploaded image
    //         foreach ($request->file('iec_images', []) as $index => $file) {

    //             // Save temporarily to storage/app/temp_uploads
    //             $path = $file->store('temp_uploads');

    //             UploadIECImageJob::dispatch(
    //                 $path,
    //                 $folderPath,
    //                 'Image_' . ($index + 1)
    //             );
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Accomplishment saved. Images are now uploading in the background.'
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    //WORKING
    // public function upload_iec(Request $request)
    // {
    //     $validated = $request->validate([
    //         'protected_area_id' => ['required'],
    //         'accomplishments'   => ['required', 'array'],
    //         'quarter'           => 'required|integer|between:1,4',
    //         'iec_images'        => ['nullable', 'array', 'max:15'],
    //         'iec_images.*'      => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
    //     ]);

    //     try {

    //         DB::beginTransaction();

    //         // Save accomplishment reports first
    //         foreach ($validated['accomplishments'] as $i => $accomplishment) {

    //             $this->insert_iec_report(
    //                 $accomplishment,
    //                 $validated['quarter'], // pass quarter here
    //                 $request->details[$i] ?? null,
    //                 $validated['protected_area_id']
    //             );
    //         }

    //         DB::commit();

    //         $folderPath = '5. PAMBCS/2026/IEC_CEPA';

    //         // Queue every uploaded image
    //         foreach ($request->file('iec_images', []) as $index => $file) {

    //             // Save temporarily to storage/app/temp_uploads
    //             $path = $file->store('temp_uploads');

    //             UploadIECImageJob::dispatch(
    //                 $path,
    //                 $folderPath,
    //                 'Image_' . ($index + 1)
    //             );
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Accomplishment saved. Images are now uploading in the background.'
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function upload_iec(Request $request)
    {
        $validated = $request->validate([
            'protected_area_id' => ['required'],
            'accomplishments'   => ['required', 'array'],
            'quarter'           => 'required|integer|between:1,4',
            'iec_images'        => ['nullable', 'array', 'max:15'],
            'iec_images.*'      => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        try {

            DB::beginTransaction();

            $reportIds = [];

            // Save accomplishment reports first
            foreach ($validated['accomplishments'] as $i => $accomplishment) {

                $report = $this->insert_iec_report(
                    $accomplishment,
                    $validated['quarter'],
                    $request->details[$i] ?? null,
                    $validated['protected_area_id']
                );

                // store the created report id
                $reportIds[] = $report->id;
            }

            DB::commit();

            $folderPath = '5. PAMBCS/2026/IEC_CEPA';

            // Example: attach all uploaded images to the FIRST accomplishment report
            $iecCepaId = $reportIds[0] ?? null;

            foreach ($request->file('iec_images', []) as $index => $file) {

                $path = $file->store('temp_uploads');

                UploadIECImageJob::dispatch(
                    $path,
                    $folderPath,
                    'PA_ID-'.$validated['protected_area_id'].'-' . ($index + 1),
                    $iecCepaId
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Accomplishment saved. Images are now uploading in the background.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    private function insert_iec_report($accomplishment, $quarter, $details, $protectedAreaId)
    {
        return IECCEPAReport::create([
            'pa_id' => $protectedAreaId,
            'quarter' => $quarter,
            'activities' => $accomplishment,
            'details' => $details,
        ]);
    }
}
