<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyErpApiKey
{
    public function handle($request, Closure $next)
    {
        // Ambil Header Dari Request (Client Harus Kirim ERP-API-KEY)
        $apiKey = $request->header('ERP-API-KEY'); 

        // Ambil Expected Key Dari Config/services.php -> .env
        $expected = config('services.erp.api_key');

        // Validasi
        if (!$apiKey || !hash_equals((string) $expected, (string) $apiKey)) {
            return response()->json(['message' => 'Unauthorized Access - Akses Dilarang'], 401);
        }

        return $next($request);
    }

}

?>