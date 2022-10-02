<?php

namespace App\Http\Controllers\SSO;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SSOController extends Controller
{
    /**
     * Login function, using SSO on the main SSO application.
     */
    public function login(Request $request) {
        $state = Str::random(32);
        $request->session()->put("state", $state);
        $query = http_build_query([
            'client_id' => config("auth.client_id"),
            'redirect_uri' => config("auth.callback"),
            "response_type" => "code",
            "scope" => config("auth.scopes"),
            "state" => $state
        ]);
        return redirect(config("auth.sso_host") . "/oauth/authorize?" . $query);
    }

    /**
     * Callback for the SSO authentication
     */
    public function callback(Request $request) {
        $state = $request->session()->pull("state");
        if ($state != $request->state) {
            throw new InvalidArgumentException();
        }
        $response = Http::asForm()->post(
            config("auth.sso_host") . "/oauth/token",
            [
            "grant_type" => "authorization_code",
            "client_id" => config("auth.client_id"),
            "client_secret" => config("auth.client_secret"),
            "redirect_uri" => config("auth.callback"),
            "code" => $request->code,
            "scope" => "*"
        ]);
        $request->session()->put($response->json());
        return redirect("/sso/authuser");
    }

    /**
     * Convert SSO authentication to own login.
     */ 
    public function getUser(Request $request) {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => 'application/json',
            "Authorization" => "Bearer " . $access_token
        ])->get(config("auth.sso_host") . "/api/user");
        $userData = $response->json();
        $email = $userData['email'];

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => $userData['email_verified_at']
            ]);
        }
        Auth::login($user);
        return redirect(route("home"));
    }
}
