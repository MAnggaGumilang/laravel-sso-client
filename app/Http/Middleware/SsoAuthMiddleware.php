<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SsoAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan token ada di session Client
        if (!Session::has('token')) {
            return redirect()->route('auth.index');
        }

        $token = Session::get('token');
        if (isset($token['error'])) {
            return redirect()->route('auth.index');
        }

        // Validasi token ke IdP
        $response = Http::withToken($token['access_token'])
            ->get('http://localhost:8000/api/token/check');

        if ($response->status() !== 200) {
            return redirect()->route('auth.index');
        }

        // Simpan data user untuk dipakai di /home
        Session::put('user', $response->json()['user']);

        return $next($request);
    }
}
