<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SecureStream;

class EncryptPageController extends Controller
{
    public function index()
    {
        return view('encrypt');
    }

    public function generate(Request $request)
    {
        $request->validate([
            "url" => "required|url"
        ]);

        $token = SecureStream::encryptUrl($request->url);
        $secureUrl = url("/stream/$token");

        return view('encrypt', compact("secureUrl"));
    }
}
