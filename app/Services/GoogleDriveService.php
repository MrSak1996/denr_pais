<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ApplicantAttachments\AttachmentsModel;
use App\Models\Chainsaw\ChainsawInformation;

class GoogleDriveService
{

    public function storeSingleAttachment($applicationNo, $uploadedBy, $file, $applicationId, $folderStructure, $folderType, $checklistEntryId)
    {
        try {

            $folderPath = "{$folderStructure}/{$folderType}";
            $this->ensureFolderExists($folderPath);

            $originalExt = $file->getClientOriginalExtension();

            $fileName = strtolower(str_replace(' ', '_', $folderType))
                . "_{$applicationNo}.{$originalExt}";

            $filePath = "{$folderPath}/{$fileName}";

            // Upload to Google Drive
            $fileId = $this->uploadToDriveAndGetFileId($file, $filePath);

            if (!$fileId) {
                throw new \Exception("Unable to retrieve file ID.");
            }

            $fileUrl = "https://drive.google.com/file/d/{$fileId}/preview";

            // ✅ SAVE WITH checklist_entry_id
            $uploadedFile = AttachmentsModel::create([
                'application_id' => $applicationId,
                'uploaded_by' => $uploadedBy,
                'checklist_entry_id' => $checklistEntryId, // 🔥 FIXED
                'file_id' => $fileId,
                'file_name' => $fileName,
                'file_url' => $fileUrl,
            ]);

            return [
                'status' => true,
                'file_id' => $fileId,
                'file_name' => $fileName,
                'file_url' => $fileUrl,
                'attachment_id' => $uploadedFile->id
            ];
        } catch (\Exception $e) {

            Log::error("Upload error: " . $e->getMessage());

            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function storeResubmissionAttachment(
        $file,
        $folderStructure,
        $folderType,
        $customFileName
    ) {
        try {
            /**
             * ✅ Ensure folder exists: CHAINSAW_PERMITTING/.../Request Letter
             */
            $folderPath = "{$folderStructure}/{$folderType}";
            $this->ensureFolderExists($folderPath);

            $fileName = $customFileName;
            $filePath = "{$folderPath}/{$fileName}";

            /**
             * ✅ Upload to Google Drive
             */
            $fileId = $this->uploadToDriveAndGetFileId($file, $filePath);

            if (!$fileId) {
                throw new \Exception("Unable to retrieve file ID.");
            }

            $fileUrl = "https://drive.google.com/file/d/{$fileId}/preview";

            /**
             * ❌ NO DB SAVE HERE
             * Controller handles DB
             */
            return [
                'status' => true,
                'file_id' => $fileId,
                'file_name' => $fileName,
                'file_url' => $fileUrl,
            ];
        } catch (\Exception $e) {
            Log::error("Upload error: " . $e->getMessage());

            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function uploadToDriveAndGetFileId($file, $filePath)
    {
        // Upload the file
        Storage::disk('google')->write($filePath, file_get_contents($file));

        // Give Google Drive a moment to sync
        usleep(500000); // 0.5 seconds

        // Search for the uploaded file in Google Drive
        $pathParts = explode('/', $filePath);
        array_pop($pathParts); // remove file name
        $folderPath = implode('/', $pathParts);

        $files = Storage::disk('google')->listContents($folderPath, true);

        $fileMeta = collect($files)->first(function ($f) use ($filePath) {
            return isset($f['path']) && $f['path'] === $filePath;
        });

        return $fileMeta['extraMetadata']['id'] ?? null;
    }

    private function ensureFolderExists($folderPath)
    {
        try {
            // Remove trailing slash if present
            $folderPath = rtrim($folderPath, '/');
            $parentDir = dirname($folderPath);
            $dirName = basename($folderPath);

            $contents = Storage::disk('google')->listContents($parentDir === '.' ? '' : $parentDir, false);

            $folder = collect($contents)->first(function ($item) use ($dirName) {
                return $item['type'] === 'dir' && $item['basename'] === $dirName;
            });

            if (!$folder) {
                Storage::disk('google')->makeDirectory($folderPath);
                Log::info("Created folder structure: {$folderPath}");
                print_r("Created folder structure: {$folderPath}");
                // Wait for Google Drive to register the new folder
                sleep(5); // increase to 2-3 seconds if needed
            }
        } catch (\Exception $e) {
            Log::warning("Could not create/verify folder {$folderPath}: " . $e->getMessage());
        }
    }

    // =====================
    //
    //
    //
    // ======================

    /**
     * Replace the attachment file on Google Drive.
     * - Deletes old file (if found by file_id)
     * - Uploads the new file into the same folder as the old file (or falls back to app folder)
     */
    public function replaceAttachment($folderPath, $oldFileId, $newFile, $applicationNo, $filePrefix)
    {
        try {
            // ✅ Step 1. Delete old file if it exists
            $oldFilePath = $this->getDriveFilePath($oldFileId, $folderPath);

            if ($oldFilePath) {
                try {
                    Storage::disk('google')->delete($oldFilePath);
                    Log::info("Deleted old file from Google Drive: {$oldFilePath} (File ID: {$oldFileId})");
                } catch (\Exception $e) {
                    Log::warning("Could not delete old file (ID: {$oldFileId}): " . $e->getMessage());
                }
            } else {
                Log::warning("Old file not found in Google Drive for ID: {$oldFileId}");
            }

            // ✅ Step 2. Generate new file name
            $originalExt = strtolower($newFile->getClientOriginalExtension());
            $sanitizedApplicationNo = strtoupper($applicationNo);

            // Always final format: prefix_APPLICATIONNO.ext
            $fileName = "{$filePrefix}_{$sanitizedApplicationNo}.{$originalExt}";

            $newFilePath = trim($folderPath, '/') . '/' . $fileName;

            // ✅ Step 3. Ensure the folder exists
            $this->ensureFolderExists($folderPath);

            // ✅ Step 4. Upload new file
            Storage::disk('google')->write($newFilePath, file_get_contents($newFile));

            // Allow Google Drive to sync
            usleep(500000); // 0.5 seconds

            // ✅ Step 5. Retrieve metadata for uploaded file
            $files = Storage::disk('google')->listContents($folderPath, true);

            $fileMeta = collect($files)->first(function ($f) use ($fileName, $folderPath) {
                return isset($f['path']) && $f['path'] === trim($folderPath, '/') . '/' . $fileName;
            });

            if (!$fileMeta || !isset($fileMeta['extraMetadata']['id'])) {
                throw new \Exception("Failed to retrieve Google Drive file ID for {$fileName}");
            }

            $newFileId = $fileMeta['extraMetadata']['id'];
            $newFileUrl = "https://drive.google.com/file/d/{$newFileId}/preview";

            Log::info("File replaced successfully. ID: {$newFileId}, Name: {$fileName}");

            return [
                'status' => true,
                'file_id' => $newFileId,
                'file_name' => $fileName,
                'file_url' => $newFileUrl,
            ];
        } catch (\Exception $e) {
            Log::error("Error replacing attachment: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Get full Google Drive file path by fileId
     */
    private function getDriveFilePath($fileId, $folderPath)
    {
        try {
            // Get all files recursively in the folder
            $files = Storage::disk('google')->listContents($folderPath, true);

            if (empty($files)) {
                Log::warning("No files found in Google Drive folder: {$folderPath}");
                return null;
            }

            // Match file by its Google Drive ID
            $file = collect($files)->first(function ($f) use ($fileId) {
                return isset($f['extraMetadata']['id']) && $f['extraMetadata']['id'] === $fileId;
            });

            if ($file && isset($file['path'])) {
                // ✅ Extract the directory (remove filename)
                return dirname($file['path']);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error finding Google Drive file path: " . $e->getMessage());
            return null;
        }
    }
    public function storeIECCEPA($path, $folderPath, $folderName)
    {
        try {

            $this->ensureFolderExists($folderPath);

            $fullPath = Storage::path($path);

            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

            $fileName = strtolower(str_replace(' ', '_', $folderName))
                . '_' . now()->format('YmdHis')
                . '.' . $extension;

            $drivePath = "{$folderPath}/{$fileName}";

            $fileId = $this->uploadToDriveAndGetFileId(
                $fullPath,
                $drivePath
            );

            if (!$fileId) {
                throw new \Exception('Unable to retrieve Google Drive file ID.');
            }

            return [
                'status'    => true,
                'file_id'   => $fileId,
                'file_name' => $fileName,
                'file_url'  => "https://drive.google.com/file/d/{$fileId}/preview",
            ];
        } catch (\Exception $e) {

            Log::error($e);

            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    // public function storeIECCEPA($file, $folderPath, $folderName)
    // {
    //     try {

    //         // Ensure folder exists
    //         $this->ensureFolderExists($folderPath);
         

    //         $extension = $file->getClientOriginalExtension();

    //         $fileName = strtolower(str_replace(' ', '_', $folderName))
    //             . '_' . now()->format('YmdHis')
    //             . '.' . $extension;

    //         $filePath = "{$folderPath}/{$fileName}";
          

    //         // Upload to Google Drive
    //         $fileId = $this->uploadToDriveAndGetFileId($file, $filePath);

    //         if (!$fileId) {
    //             throw new \Exception('Unable to retrieve Google Drive file ID.');
    //         }

    //         return [
    //             'status'    => true,
    //             'file_id'   => $fileId,
    //             'file_name' => $fileName,
    //             'file_url'  => "https://drive.google.com/file/d/{$fileId}/preview",
    //         ];
    //     } catch (\Exception $e) {

    //         Log::error('IEC Upload Error: ' . $e->getMessage());

    //         return [
    //             'status' => false,
    //             'error'  => $e->getMessage(),
    //         ];
    //     }
    // }







    /**
     * THIS IS WORKING ON COMPANY_APPLY
     * Helper function to build Google Drive file path from ID
     */
    // private function getDriveFilePath($fileId, $folderPath)
    // {
    //     try {
    //         // Ensure full recursive search
    //         $files = Storage::disk('google')->listContents($folderPath, true);

    //         if (empty($files)) {
    //             Log::warning("No files found in Google Drive folder: {$folderPath}");
    //             return null;
    //         }

    //         // Find file by matching the file_id
    //         $file = collect($files)->first(function ($f) use ($fileId) {
    //             return isset($f['extraMetadata']['id']) && $f['extraMetadata']['id'] === $fileId;
    //         });

    //         if (!$file) {
    //             Log::warning("File with ID {$fileId} not found in folder: {$folderPath}");
    //             return null;
    //         }

    //         return $file['path'] ?? null;
    //     } catch (\Exception $e) {
    //         Log::error("Error finding Google Drive file path: " . $e->getMessage());
    //         return null;
    //     }
    // }


}
