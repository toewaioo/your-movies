<?php

namespace App\Helpers;

class MimeDetector
{
    public static function detect($url, $remoteHeaders = [])
    {
        // 1. Check remote header Content-Type
        foreach ($remoteHeaders as $h) {
            if (stripos($h, "Content-Type:") === 0) {
                return trim(substr($h, 13));
            }
        }

        // 2. Detect by extension
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        $map = self::mimeMap();

        if (isset($map[$ext])) {
            return $map[$ext];
        }

        // 3. Fallback: Use PHP finfo (best detection)
        $tmp = @file_get_contents($url, false, stream_context_create(["http" => ["method" => "HEAD"]]));

        if ($tmp !== false) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $tmp);
            if ($mime) return $mime;
        }

        // 4. Worst case fallback
        return "application/octet-stream";
    }

    private static function mimeMap()
    {
        return [
            // Video formats
            "mp4" => "video/mp4",
            "m4v" => "video/x-m4v",
            "webm" => "video/webm",
            "mov" => "video/quicktime",
            "mkv" => "video/x-matroska",
            "avi" => "video/x-msvideo",
            "ts" => "video/mp2t",
            "m3u8" => "application/x-mpegURL",

            // Audio formats
            "mp3" => "audio/mpeg",
            "aac" => "audio/aac",
            "wav" => "audio/wav",
            "ogg" => "audio/ogg",
            "flac" => "audio/flac",

            // Image formats
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png",
            "gif" => "image/gif",
            "webp" => "image/webp",

            // Documents
            "pdf" => "application/pdf",
            "txt" => "text/plain",
            "json" => "application/json",
            "csv" => "text/csv",

            // Archives
            "zip" => "application/zip",
            "rar" => "application/vnd.rar",
            "7z" => "application/x-7z-compressed",
        ];
    }
}
