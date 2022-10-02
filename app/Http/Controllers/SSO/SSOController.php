<?php

namespace App\Http\Controllers\SSO;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class SSOController extends Controller
{
    public function login(Request $request) {
        $state = Str::random(32);
        $request->session()->put("state", $state);
        $query = http_build_query([
            'client_id' => "8",
            'redirect_uri' => "http://127.0.0.1:8001/callback",
            "response_type" => "code",
            "scope" => "*",
            "state" => $state
        ]);
        return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);
    }

    public function callback(Request $request) {
        $state = $request->session()->pull("state");
        if ($state != $request->state) {
            throw new InvalidArgumentException();
        }
        $response = Http::asForm()->post(
            "http://127.0.0.1:8000/oauth/token",
            [
            "grant_type" => "authorization_code",
            "client_id" => "8",
            "client_secret" => "Gx66lyGAyy7sYHsefNw7NXZj6gYpKwYo0du2cd4v",
            "redirect_uri" => "http://127.0.0.1:8001/callback",
            "code" => $request->code,
            "scope" => "*"
        ]);
        $request->session()->put($response->json());
        return redirect("/sso/authuser");
    }

    public function getUser(Request $request) {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => 'application/json',
            "Authorization" => "Bearer " . $access_token
        ])->get("http://127.0.0.1:8000/api/user");
        return $response->json();
    }
}
