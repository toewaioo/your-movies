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
                "method" => "GET",
                "header" => implode("\r\n", $headers)
            ]
        ];

        $context = stream_context_create($opts);

        $stream = fopen($url, "rb", false, $context);

        foreach ($http_response_header as $header) {
            if (stripos($header, 'Content-Type:') === 0) header($header);
            if (stripos($header, 'Content-Length:') === 0) header($header);
            if (stripos($header, 'Accept-Ranges:') === 0) header($header);
            if (stripos($header, 'Content-Range:') === 0) header($header);
        }

        fpassthru($stream);
        exit;
    }
}
