<?php

namespace App\Http\Controllers;

use App\Models\File;

use Illuminate\Http\Request;
use Zxing\QrReader;
use Illuminate\Support\Facades\Storage;
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
        $qrContent = $qrcode->text();

        // Split the QR code content by a delimiter to extract the parts
        $parts = explode('/', $qrContent);

        // Get the file ID from the parts (assuming it's the fourth part)
        $fileId = $parts[4];


        // Decrypt the encrypted token (assuming it's the last part)
        $encryptedToken = end($parts);
        $decryptedToken = Crypt::decrypt($encryptedToken);

        // Combine the decrypted token with the URL
        $url = route('file-integrity-check', ['file' => $fileId, 'token' => $decryptedToken]);

        // If it's a valid URL, redirect to the link
        return redirect()->away($url);

    }




    public function showResult(Request $request)
    {
        $data = $request->query('data');
        $result = $data;
        return view('result', ['result' => $result]);
    }
}
