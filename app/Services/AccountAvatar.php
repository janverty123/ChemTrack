<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountAvatar
{
    public function store(User $user, UploadedFile $file): void
    {
        if (! extension_loaded('gd')) {
            throw ValidationException::withMessages(['avatar' => 'Photo uploads are temporarily unavailable. Your current avatar has been kept.']);
        }

        $image = @imagecreatefromstring($file->get());
        if (! $image) {
            throw ValidationException::withMessages(['avatar' => 'Choose a valid JPEG, PNG or WebP image.']);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $side = min($width, $height);
        $output = imagecreatetruecolor(256, 256);
        imagecopyresampled($output, $image, 0, 0, (int) (($width - $side) / 2), (int) (($height - $side) / 2), 256, 256, $side, $side);
        ob_start();
        imagejpeg($output, null, 88);
        $encoded = ob_get_clean();
        imagedestroy($image);
        imagedestroy($output);
        $path = 'avatars/'.Str::uuid().'.jpg';
        if (! Storage::disk('local')->put($path, $encoded)) {
            throw ValidationException::withMessages(['avatar' => 'Your photo could not be saved. Please try again.']);
        }
        $old = $user->avatar_path;
        try {
            $user->forceFill(['avatar_path' => $path])->saveOrFail();
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }
    }
}
