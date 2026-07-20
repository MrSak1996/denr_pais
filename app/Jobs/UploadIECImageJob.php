<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Storage;
use App\Models\IECCEPA\IECCEPAModel;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class UploadIECImageJob implements ShouldQueue
{

    /**
     * Create a new job instance.
     */
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;

    public function __construct(
        public string $path,
        public string $folderPath,
        public string $fileName,
        public int $iecCepaId
    ) {}

    public function handle(GoogleDriveService $driveService)
    {
        $fullPath = storage_path('app/private/' . $this->path);

        $upload = $driveService->storeIECCEPA(
            $this->path,
            $this->folderPath,
            $this->fileName
        );

        if (!$upload['status']) {
            throw new \Exception($upload['error']);
        }

        IECCEPAModel::create([
            'iec_cepa_id' => $this->iecCepaId, // <-- ADD THIS
            'report_type' => 'iec_cepa',
            'file_id' => $upload['file_id'],
            'file_name' => $upload['file_name'],
            'file_url' => $upload['file_url'],
            
        ]);
  
        Storage::delete($this->path);
    }

    /**
     * Execute the job.
     */
}
