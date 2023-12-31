<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\QRCodeController;
use Illuminate\Support\Facades\Route;


use App\Models\Signature;

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
    $userId = auth()->id();
    $signature = Signature::where('user_id', $userId)->first();

    return view('dashboard', ['signature' => $signature]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/send-file', [FileController::class, 'showSendFileForm'])->name('send-file');
    Route::post('/send-file', [FileController::class, 'storeFile'])->name('store-file');
    Route::get('/files', [FileController::class, 'showFiles'])->name('show-files');
    Route::get('/received-files', [FileController::class, 'showReceivedFiles'])->name('received-files');
    Route::get('/files/{file}/download', [FileController::class, 'downloadFile'])->name('download-file');
    Route::get('/files/{file}/decrypt', [FileController::class, 'decrypt'])->name('decrypt-file');
    Route::get('/files/{file}/encrypt', [FileController::class, 'encrypt'])->name('encrypt-file');
    Route::post('/files/{file}/encrypt', [FileController::class, 'storeEncrypt'])->name('store-encrypt');
    Route::post('/files/{file}/decrypt', [FileController::class, 'storeDecrypt'])->name('store-decrypt');
    Route::get('/files/{file}/add-signature', [FileController::class, 'addSignature'])->name('add-signature');
    Route::get('/files/{file}/integrity-check/{token}', [FileController::class, 'fileIntegrityCheck'])->name('file-integrity-check');
    Route::get('/preview-pdf/{fileId}', [QRCodeController::class, 'previewPdf'])->name('preview-pdf');




    Route::resource('signatures', SignatureController::class)->names([
        'index' => 'signatures.index',
        'create' => 'signatures.create',
        'store' => 'signatures.store',
        'show' => 'signatures.show',
        'edit' => 'signatures.edit',
        'update' => 'signatures.update',
        'destroy' => 'signatures.destroy',
    ]);
});



Route::get('/scan', [QRCodeController::class, 'showScanForm'])->name('scan.form');
Route::post('/scan', [QRCodeController::class, 'scan'])->name('scan');
Route::get('/result', [QRCodeController::class, 'showResult'])->name('result');

Route::get('/sign-file/{file}', [FileController::class, 'signFile'])->name('sign-file');
Route::get('/verify-file/{file}', [FileController::class, 'verifyFile'])->name('verify-file');



Route::get('/qrcode-scanner', function () {
    return view('qrcode-scanner');
});

Route::get('/scanner', function () {
    return view('scanner');
});
// routes/web.php



Route::post('/scan-qrcode', [QRCodeController::class, 'scanQRCode'])->name('scanQRCode');


require __DIR__ . '/auth.php';
