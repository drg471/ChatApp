<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success($data = null, $message = 'Operación realizada con éxito.', $status = 200)
    {
        return response()->json([
            'succes' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error($message = 'Ha ocurrido un error.', $status = 500, $data = null)
    {
        return response()->json([
            'succes' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
