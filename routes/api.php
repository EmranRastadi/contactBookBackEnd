<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
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


Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('contacts/add', [UserController::class, 'addContacts'])->name('addContacts');
    Route::get('contacts/data-all/{token}/{paginate?}', [UserController::class, 'dataPagintae'])->name('dataPaginate');
    Route::post('contacts/edit-single/{id}', [UserController::class, 'editSingleData'])->name('singleEdit');
    Route::post('contacts/delete-single/{id}', [UserController::class, 'deleteContacts'])->name('singleDelete');
    Route::get('contacts/get-single/{id}', [UserController::class, 'getSingleContact'])->name('singleGet');

    Route::get('contacts/search/{search}/{token}/{paginate?}', [UserController::class, 'searchData'])->name('searchData');
});

