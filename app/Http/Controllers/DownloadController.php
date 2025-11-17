<?php

namespace App\Http\Controllers;

use App\Helpers\SecureStream;
use App\Helpers\MimeDetector;

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
        $stream = @fopen($url, "rb");

        if (!$stream) abort(404, "File not accessible");

        // Auto detect MIME type
        $mime = MimeDetector::detect($url);

        // Guess filename from URL
        $name = basename(parse_url($url, PHP_URL_PATH)) ?: "download.bin";

        // Force download headers
        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"$name\"");
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: bytes");

        // Optional: If remote headers include content-length
        global $http_response_header;
        foreach ($http_response_header as $h) {
            if (stripos($h, "Content-Length:") === 0) {
                header($h);
            }
        }

        fpassthru($stream);
        exit;
    }
}
