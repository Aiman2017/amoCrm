<?php

use App\Http\Controllers\AmoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'amo-crm', 'as' => 'amoCrm.'], function () {

    Route::get('/', [AmoController::class, 'index'])->name('index');
    Route::get('/contacts-without-deals', [AmoController::class, 'getContactsWithoutDeals'])->name('contacts.without.deals');
    Route::post('/create-task', [AmoController::class, 'createTask'])->name('task.create');
    Route::post('/close-task', [AmoController::class, 'closeTask'])->name('task.close');

});
