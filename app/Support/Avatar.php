<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

// Avatars live in public/uploads (not storage/) because many shared hosts forbid the storage symlink.
class Avatar
{
    // Re-encode with GD: strips metadata (e.g. GPS) and anything hidden in the file.
    public static function store(UploadedFile $file): ?string
    {
        $source = @imagecreatefromstring($file->get());
        if (! $source) {
            return null;
        }

        $size = min(imagesx($source), imagesy($source));
        $avatar = imagecreatetruecolor(256, 256);
        imagecopyresampled($avatar, $source, 0, 0,
            intdiv(imagesx($source) - $size, 2), intdiv(imagesy($source) - $size, 2),
            256, 256, $size, $size);

        $dir = public_path('uploads/avatars');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = Str::random(32).'.jpg';
        imagejpeg($avatar, "$dir/$name", 85);

        return "uploads/avatars/$name";
    }

    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/avatars/')) {
            @unlink(public_path($path));
        }
    }
}
