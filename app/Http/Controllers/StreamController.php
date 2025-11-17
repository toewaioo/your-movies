<?php

namespace App\Http\Controllers;

use App\Helpers\SecureStream;

class StreamController extends Controller
{
    public function stream($token)
    {
        $url = SecureStream::decryptUrl($token);

        if (!$url) abort(403, "Invalid token");

        return $this->proxyStream($url);
    }

    private function proxyStream($url)
    {
        $headers = [];

        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = "Range: " . $_SERVER['HTTP_RANGE'];
        }

        $opts = [
            "http" => [
                "method"  => "GET",
                "header"  => implode("\r\n", $headers)
            ]
        ];

        $context = stream_context_create($opts);

        $stream = @fopen($url, "rb", false, $context);

        if (!$stream) {
            abort(404, "Unable to open remote file.");
        }

        // Default MIME if remote does not send one
        $mimeType = "video/mp4";

        // Read remote headers
        foreach ($http_response_header as $h) {
            if (stripos($h, "Content-Type:") === 0) {
                header($h);
                $mimeType = trim(substr($h, 13));
            }

            if (stripos($h, "Content-Length:") === 0) header($h);
            if (stripos($h, "Content-Range:") === 0)  header($h);
            if (stripos($h, "Accept-Ranges:") === 0) header($h);
        }

        // If server never sent MIME → manually set correct one
        if (!headers_sent()) {
            header("Content-Type: $mimeType");
            header("Accept-Ranges: bytes");
        }

        fpassthru($stream);
        exit;
    }
}
