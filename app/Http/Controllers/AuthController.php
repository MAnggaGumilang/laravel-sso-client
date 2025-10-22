<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AuthController extends Controller
{
    // 1) Arahkan user ke halaman authorize milik IdP
    public function index(Request $request)
    {
        // Simpan state acak untuk proteksi CSRF (OAuth2)
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id'     => env('SSO_CLIENT_ID'),
            'redirect_uri'  => env('SSO_CLIENT_CALLBACK_PATH'),
            'response_type' => 'code',
            'scope'         => '',
            'state'         => $state,
        ]);

        // Arahkan ke IdP (Passport) -> halaman authorize
        return redirect('http://localhost:8000/oauth/authorize?' . $query);
    }

    // 2) Terima callback dari IdP, verifikasi state, tukar code → token
    public function ssoCallback(Request $request)
    {
        $state = $request->session()->pull('state');

        // Wajib: validasi state
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        // Tukar authorization code menjadi access_token di IdP
        $response = Http::asForm()->post('http://localhost:8000/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('SSO_CLIENT_ID'),
            'client_secret' => env('SSO_CLIENT_SECRET'),
            'redirect_uri'  => env('SSO_CLIENT_CALLBACK_PATH'),
            'code'          => $request->code,
        ]);

        // Simpan token ke session
        $request->session()->put('token', $token = $response->json());

        // Alihkan ke halaman terlindungi
        return redirect('/home');
    }
}
