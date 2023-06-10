<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/send-file', [FileController::class, 'showSendFileForm'])->name('send-file');
Route::post('/send-file', [FileController::class, 'storeFile'])->name('store-file');
Route::get('/files', [FileController::class, 'showFiles'])->name('showFiles');
Route::get('/received-files', [FileController::class, 'showReceivedFiles'])->name('received-files');
Route::get('/files/{file}/download', [FileController::class, 'download'])->name('download-file');
Route::get('/files/{file}/decrypt', [FileController::class, 'decrypt'])->name('decrypt-file');




require __DIR__.'/auth.php';
