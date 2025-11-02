<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function success($data = null, $message = null, $status = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function error($message = null, $status = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
