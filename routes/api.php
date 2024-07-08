<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
	'prefix' => 'users',
	'as' => 'api.users.',
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' => ['auth:sanctum']
], function(){
	Route::post('register', 'AuthController@register');
    Route::put('update/{id}','AlumniController@update');
    Route::get('list','AlumniController@getPaginatedAlumni');
    Route::get('/one/{id}','AlumniController@getOne');
    Route::get('count-per-year','AlumniController@getCountPerYear');
});

Route::group([
	'prefix' => 'admin',
	'as' => 'api.admin.',
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' => ['auth:sanctum']
], function(){
	Route::post('register', 'AdminController@register');
    Route::put('update/{id}','AdminController@update');
    Route::get('list','AdminController@getPaginatedAdmin');
    Route::get('/one/{id}','AdminController@getOne');
});

Route::group([
	'prefix' => 'message',
	'as' => 'api.message.',
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' => ['auth:sanctum']
], function(){
	Route::post('send', 'MessageController@add');
    Route::put('update/{id}','MessageController@update');
    Route::get('get-all','MessageController@getAllMessages');
    Route::get('by-user','MessageController@getMessagesByUser');
    Route::get('by-user/received/{id}','MessageController@getReceivedMessages');
});

Route::group([
	'prefix' => 'course',
	'as' => 'api.course.',
	'namespace' => 'App\Http\Controllers\Api',
	'middleware' => ['auth:sanctum']
], function(){
	Route::post('create', 'CourseController@add');
    Route::put('update/{id}','CourseController@update');
    Route::delete('update/{id}','CourseController@delete');
    Route::get('get-paginated','CourseController@getPaginated');
    Route::get('get-one','CourseController@getOne');
});

Route::group([
	'prefix' => 'auth',
	'as' => 'api.auth.',
	'namespace' => 'App\Http\Controllers\Api'
], function(){
	Route::post('login', 'AuthController@login');
});

Route::group([
	'prefix' => 'alumni',
	'as' => 'api.alumni_auth.',
	'namespace' => 'App\Http\Controllers\Api'
], function(){
	Route::post('login', 'AlumniController@login');
	Route::post('change-password-password', 'AlumniController@requiredChangePassword');
	Route::post('forgot-password', 'AlumniController@forgotPassword');
});

Route::group([
	'prefix' => 'nature_of_work',
	'as' => 'api.nature_of_work.',
	'namespace' => 'App\Http\Controllers\Api'
], function(){
	Route::post('create', 'NatureOfWorkController@add');
    Route::put('update/{id}','NatureOfWorkController@update');
    Route::delete('update/{id}','NatureOfWorkController@delete');
    Route::get('get-paginated','NatureOfWorkController@getPaginated');
    Route::get('get-one','NatureOfWorkController@getOne');
    Route::get('get-all','NatureOfWorkController@getAll');
});

Route::group([
	'prefix' => 'job_posting',
	'as' => 'api.job_posting.',
	'namespace' => 'App\Http\Controllers\Api'
], function(){
	Route::post('create', 'JobPostingController@add');
    Route::put('update/{id}','JobPostingController@update');
    Route::delete('update/{id}','JobPostingController@delete');
    Route::get('get-paginated','JobPostingController@getPaginated');
    Route::get('get-one','JobPostingController@getOne');
});
