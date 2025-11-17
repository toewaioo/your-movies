<?php

namespace App\Http\Controllers;

use App\Helpers\SecureStream;
use App\Helpers\MimeDetector;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function download($token)
    {
        $url = SecureStream::decryptUrl($token);

        if (!$url) abort(403, "Invalid token");

        return $this->forceDownload($url);
    }

    private function forceDownload($url)
    {
        // Try to get file headers
        try {
            $head = Http::head($url);
            $mime = $head->header('Content-Type') ?? 'application/octet-stream';
            $size = $head->header('Content-Length');
        } catch (\Exception $e) {
            $mime = 'application/octet-stream';
            $size = null;
        }

        // Extract filename
        $filename = basename(parse_url($url, PHP_URL_PATH));

        // Stream file to browser
        return new StreamedResponse(function () use ($url) {
            $stream = fopen($url, 'r');
            while (!feof($stream)) {
                echo fread($stream, 1024 * 16); // 16 KB chunks
                flush();
            }
            fclose($stream);
        }, 200, [
            "Content-Type" => $mime,
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Content-Length" => $size,
            "Cache-Control" => "no-cache, no-store, must-revalidate",
            "Pragma" => "no-cache",
            "Expires" => "0",
        ]);
    }
}
