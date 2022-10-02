<?php

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SSO\SSOController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get("/sso/login", [SSOController::class, 'login'])->name("sso.login");

Route::get("/callback", [SSOController::class,'callback'])->name("sso.callback");

Route::get("/sso/authuser", [SSOController::class, 'getUser']);