<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Zxing\QrReader;
use Illuminate\Support\Facades\Crypt;


class QRCodeController extends Controller
{
    public function showScanForm()
    {
        $showCameraSection = true;
        return view('scan', compact('showCameraSection'));
    }


    public function scan(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'qr_code' => 'required|file|mimes:jpeg,png|max:2048',
        ]);

        // Get the uploaded file
        $uploadedFile = $request->file('qr_code');

        // Get the file path
        $filePath = $uploadedFile->getPathname();

        // Create a new instance of QrReader
        $qrcode = new QrReader($filePath);

        // Retrieve the scanned QR code text
        $encryptedToken = $qrcode->text();

        // Decrypt the encrypted token
        $decryptedToken = Crypt::decrypt($encryptedToken);

        // Check if the decrypted token is a valid URL
        if (filter_var($decryptedToken, FILTER_VALIDATE_URL)) {
            // If it's a valid URL, redirect to the link
            return redirect()->away($decryptedToken);
        }

        // If it's not a valid URL, display the result
        return view('result', compact('decryptedToken'));
    }


    public function showResult(Request $request)
    {
        $data = $request->query('data');
        $result = $data;
        return view('result', ['result' => $result]);
    }
}
