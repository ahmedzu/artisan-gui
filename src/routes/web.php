<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ArtisanController;
use Ahmedzu\ArtisanGui\Controllers\ArtisanController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/artisan_gui', [ArtisanController::class, 'index'])->name('artisan.index');
Route::post('/artisan/execute', [ArtisanController::class, 'execute'])->name('artisan.execute');
