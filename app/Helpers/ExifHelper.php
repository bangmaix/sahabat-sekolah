<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExifHelper
{
    /**
     * Membersihkan metadata EXIF (lokasi GPS, identitas perangkat, waktu kamera)
     * dari berkas foto yang diunggah pelapor demi melindungi privasi korban/saksi.
     *
     * @return string Path relatif file yang disimpan pada disk
     */
    public static function stripAndStore(UploadedFile $file, string $directory = 'evidences', ?string $disk = null): string
    {
        $disk = $disk ?: AppStorage::privateDisk();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?: '';

        // Hanya proses gambar format JPEG / PNG / WEBP
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) || str_starts_with($mime, 'image/');

        if ($isImage && extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $tmpPath = $file->getRealPath();

            if ($tmpPath && file_exists($tmpPath)) {
                $rawContents = @file_get_contents($tmpPath);

                if ($rawContents !== false) {
                    $img = @imagecreatefromstring($rawContents);

                    if ($img !== false) {
                        $safeExt = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
                        $filename = Str::random(40).'.'.$safeExt;
                        $relativeFilePath = trim($directory, '/').'/'.$filename;

                        $tempFile = tempnam(sys_get_temp_dir(), 'ss_exif_');
                        $saved = false;

                        if ($tempFile !== false) {
                            if ($safeExt === 'png') {
                                imagealphablending($img, false);
                                imagesavealpha($img, true);
                                $saved = @imagepng($img, $tempFile);
                            } elseif ($safeExt === 'webp' && function_exists('imagewebp')) {
                                $saved = @imagewebp($img, $tempFile, 90);
                            } else {
                                $saved = @imagejpeg($img, $tempFile, 90);
                            }

                            imagedestroy($img);

                            if ($saved && file_exists($tempFile)) {
                                $stream = fopen($tempFile, 'rb');
                                if ($stream !== false) {
                                    Storage::disk($disk)->put($relativeFilePath, $stream);
                                    if (is_resource($stream)) {
                                        fclose($stream);
                                    }
                                    @unlink($tempFile);

                                    return $relativeFilePath;
                                }
                                @unlink($tempFile);
                            }
                        } else {
                            imagedestroy($img);
                        }
                    }
                }
            }
        }

        // Fallback aman: penyimpanan reguler Laravel jika bukan gambar atau GD tidak dapat mengurai
        return $file->store($directory, $disk);
    }
}
