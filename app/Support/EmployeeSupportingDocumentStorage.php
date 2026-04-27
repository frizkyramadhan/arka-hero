<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeSupportingDocumentStorage
{
    public const DISK = 'private';

    public const MIME_RULE = 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120';

    public static function storeForEmployee(UploadedFile $file, string $employeeId, string $subdir): string
    {
        $ext = $file->getClientOriginalExtension();
        $name = Str::uuid()->toString().($ext !== '' ? '.'.$ext : '');

        return $file->storeAs('employee_supporting_documents/'.$employeeId.'/'.$subdir, $name, self::DISK);
    }

    public static function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function downloadResponse(?string $path): ?BinaryFileResponse
    {
        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return response()->download(storage_path('app/private/'.$path));
    }
}
