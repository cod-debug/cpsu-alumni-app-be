<?php

use App\Mail\UserTemporaryPassword;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

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

Route::get('/login', function () {
    return response()->json([
        'status' => 'Invalid',
        'message' => 'Unauthorized User!'
    ], 401);
})->name('login');

Route::get('/email-template', function () {
    return view('emails.user-temp-password');
})->name('email-template');


Route::get('/send-test-email', function () {
    Mail::to('roy.duenas.sdtpnoli@gmail.com')->send(new UserTemporaryPassword());
})->name('send-test-email');
