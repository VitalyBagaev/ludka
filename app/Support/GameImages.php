<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class GameImages
{
    public const CASE_PLACEHOLDER = '/images/case-placeholder.svg';

    public const SKIN_PLACEHOLDER = '/images/skin-placeholder.svg';

    public static function case(?string $image): string
    {
        return self::resolve($image, self::CASE_PLACEHOLDER);
    }

    public static function skin(?string $image): string
    {
        return self::resolve($image, self::SKIN_PLACEHOLDER);
    }

    public static function resolve(?string $image, string $placeholder): string
    {
        if (! filled($image)) {
            return $placeholder;
        }

        if (str_starts_with($image, 'http') || str_starts_with($image, '/images/')) {
            return $image;
        }

        if (str_starts_with($image, '/storage/')) {
            return $image;
        }

        return Storage::disk('public')->url($image);
    }

    public static function storeUpload(?\Illuminate\Http\UploadedFile $file, string $folder, ?string $currentPath = null): ?string
    {
        if ($file === null) {
            return $currentPath;
        }

        if ($currentPath && ! str_starts_with($currentPath, '/images/')) {
            $stored = str_starts_with($currentPath, '/storage/')
                ? str_replace('/storage/', '', $currentPath)
                : $currentPath;

            if (Storage::disk('public')->exists($stored)) {
                Storage::disk('public')->delete($stored);
            }
        }

        return $file->store($folder, 'public');
    }
}
