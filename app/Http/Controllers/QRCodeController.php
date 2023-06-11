<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Zxing\QrReader;


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
        $result = $qrcode->text();

        return view('result', compact('result'));
    }

    public function showResult(Request $request)
    {
        $data = $request->query('data');
        $result = $data;
        return view('result', ['result' => $result]);
    }
}
