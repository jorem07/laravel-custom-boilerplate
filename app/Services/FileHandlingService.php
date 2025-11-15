<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Exception;

class FileHandlingService
{
    // NOTE: if you use DIGITAL OCEAN SPACE, please install the composer package first.
    // composer: composer require league/flysystem-aws-s3-v3
    // link for details: https://packagist.org/packages/league/flysystem-aws-s3-v3

    // NOTE: if you use AZURE STORAGE, please install the composer package first.
    // composer: composer require league/flysystem-azure-blob-storage
    // link for details: https://packagist.org/packages/league/flysystem-azure-blob-storage
    // create function check and convert the image
    public function upload(UploadedFile $file, ?string $table_name): string
    {
        if (empty($table_name)) {
            throw new Exception('The table name parameter is required.');
        }

        $root_folder = config('filesystems.disks.digitalocean.root_path');
        $root_folder = $root_folder ?: 'uploads';

        $fileName = $this->generateFileName($file);
        $uploadPath = $this->generateUploadPath($root_folder, $table_name);

        if (str_starts_with($file->getMimeType(), 'image/')) {
            $file = $this->convertImageToWebp($file);
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
        }

        try {
            if (config('filesystems.default') === 'digitalocean') {
                $filePath = $file->storeAs($uploadPath, $fileName, 'public');
                // $filePath = Storage::disk('digitalocean')->putFileAs($uploadPath, $file, $fileName, 'public');
            } else {
                $filePath = $file->storeAs($uploadPath, $fileName, 'public');
            }

            if (!$filePath) {
                throw new Exception('Failed to upload file.');
            }

            return $filePath;
        } catch (Exception $exception) {
            logger()->error("File upload error: {$exception->getMessage()}", [
                'file_name' => $fileName,
                'path' => $uploadPath
            ]);

            throw new Exception('File upload failed.');
        }
    }

    public function convertImageToWebp(UploadedFile $file, int $quality = 80): UploadedFile
    {
        $mime = $file->getMimeType();
        $sourcePath = $file->getRealPath();
        $tempPath = sys_get_temp_dir() . '/' . \Illuminate\Support\Str::uuid() . '.webp';

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                return $file;
            default:
                return $file;
        }

        imagewebp($image, $tempPath, $quality);
        imagedestroy($image);

        return new UploadedFile($tempPath, pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp', 'image/webp', null, true);
    }


    public function generateFileName(UploadedFile $file): string
    {
        // datetime-string(36).extension
        return now()->format('YmdHis') . '-' . Str::random(36) . '.' . $file->getClientOriginalExtension();
    }

    private function generateUploadPath(string $folder, string $tableName): string
    {
        return "{$folder}/{$tableName}";
    }

    public function getTemporaryUrl($filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        return Storage::disk('digitalocean')->temporaryUrl($filePath, now()->addMinutes(config('filesystems.disks.digitalocean.expiration', 30)));
    }

    public function removeFile($path)
    {
        if (empty($path)) {
            return;
        }
        
        $disk = Storage::disk(env('FILESYSTEM_DISK'));
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
