<?php

namespace App\Http\Controllers;

use App\Models\File;

use Illuminate\Http\Request;
use Zxing\QrReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use Barryvdh\DomPDF\PDF;
use Dompdf\Dompdf;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;


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

        // Ensure that the uploaded file is an image
        if (!$uploadedFile->isValid() || !in_array($uploadedFile->getClientOriginalExtension(), ['jpeg', 'jpg', 'png'])) {
            return redirect()->back()->with('error', 'Invalid file format.');
        }

        // Get the file path
        $filePath = $uploadedFile->getPathname();

        try {
            // Create a new instance of QrReader
            $qrcode = new QrReader($filePath);

            // Retrieve the scanned QR code text
            $qrContent = $qrcode->text();

            // Split the QR code content by a delimiter to extract the parts
            $parts = explode('/', $qrContent);

            // Check if the QR code content matches the expected format
            if (count($parts) < 5 || !ctype_digit($parts[4])) {
                return redirect()->back()->with('error', 'Invalid QR code content.');
            }

            // Get the file ID from the parts
            $fileId = $parts[4];

            // Decrypt the encrypted token (assuming it's the last part)
            $encryptedToken = end($parts);
            try {
                $decryptedToken = Crypt::decrypt($encryptedToken);
            } catch (DecryptException $e) {
                return redirect()->back()->with('error', 'An error occurred while decrypting the token.');
            }

            // Combine the decrypted token with the URL
            $url = route('file-integrity-check', ['file' => $fileId, 'token' => $decryptedToken]);

            // If it's a valid URL, redirect to the link
            return redirect()->away($url);
        } catch (Exception $e) {
            // Handle any exceptions here if needed
            return redirect()->back()->with('error', 'An error occurred while processing the QR code.');
        }
    }





    // public function scan(Request $request)
    // {
    //     // Validate the uploaded file
    //     $request->validate([
    //         'qr_code' => 'required|file|mimes:jpeg,png|max:2048',
    //     ]);

    //     // Get the uploaded file
    //     $uploadedFile = $request->file('qr_code');

    //     // Get the file path
    //     $filePath = $uploadedFile->getPathname();

    //     // Create a new instance of QrReader
    //     $qrcode = new QrReader($filePath);

    //     // Retrieve the scanned QR code text
    //     $qrContent = $qrcode->text();

    //     // Split the QR code content by a delimiter to extract the parts
    //     $parts = explode('/', $qrContent);

    //     // Get the file ID from the parts (assuming it's the fourth part)
    //     $fileId = $parts[4];


    //     // Decrypt the encrypted token (assuming it's the last part)
    //     $encryptedToken = end($parts);


    //     $decryptedToken = Crypt::decrypt($encryptedToken);

    //     // Combine the decrypted token with the URL
    //     $url = route('file-integrity-check', ['file' => $fileId, 'token' => $decryptedToken]);

    //     // If it's a valid URL, redirect to the link
    //     return redirect()->away($url);
    // }

    // public function previewPdf($fileId)
    // {
    //     // Retrieve the file based on the provided file ID
    //     $file = File::findOrFail($fileId);

    //     // Determine the directory based on the file status
    //     $directory = '';

    //     switch ($file->status) {
    //         case 'uploaded':
    //             $directory = 'files';
    //             break;
    //         case 'signed':
    //             $directory = 'signed_files';
    //             break;
    //         case 'encrypted':
    //             $directory = 'encrypted_files';
    //             break;
    //         case 'decrypted':
    //             $directory = 'decrypted_files';
    //             break;
    //         default:
    //             // Handle the case when the file status is unknown
    //             return redirect()->back()->with('error', 'Unknown file status.');
    //     }

    //     // Define the path to the file
    //     $filePath = storage_path('app/' . $directory . '/' . $file->unique_file_name);

    //     dd($filePath);

    //     // Load the file using PhpWord
    //     $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);

    //     // Generate a unique name for the PDF file
    //     $pdfFileName = time() . '_' . $file->original_file_name . '.pdf';

    //     // Define the path for the PDF file
    //     $pdfFilePath = storage_path('app/pdf_files/' . $pdfFileName);

    //     // Configure PhpWord to use the Dompdf PDF renderer
    //     \PhpOffice\PhpWord\Settings::setPdfRenderer(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));

    //     // Save the PhpWord document as PDF
    //     $phpWord->save($pdfFilePath, 'PDF');

    //     // Return the PDF file as a response
    //     return response()->file($pdfFilePath, [
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"',
    //     ]);
    // }

    public function previewPdf($fileId)
    {
        // Retrieve the file based on the provided file ID
        $file = File::findOrFail($fileId);

        // Determine the directory and file name based on the file status
        $directory = '';
        $fileName = '';

        switch ($file->status) {
            case 'uploaded':
                $directory = 'files';
                $fileName = $file->unique_file_name;
                break;
            case 'signed':
                $directory = 'signed_files';
                $fileName = $file->unique_file_name;
                break;
            case 'encrypted':
                $directory = 'encrypted_files';
                $fileName = $file->encrypted_file_name;
                break;
            case 'decrypted':
                $directory = 'decrypted_files';
                $fileName = $file->decrypted_file_name;
                break;
            default:
                // Handle the case when the file status is unknown
                return redirect()->back()->with('error', 'Unknown file status.');
        }

        // Define the path to the file
        $filePath = storage_path('app/' . $directory . '/' . $fileName);

        // Load the file using PhpWord
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);

        // Generate a unique name for the PDF file
        $pdfFileName = time() . '_' . $file->original_file_name . '.pdf';

        // Define the path for the PDF file
        $pdfFilePath = storage_path('app/pdf_files/' . $pdfFileName);

        // Configure PhpWord to use the Dompdf PDF renderer
        \PhpOffice\PhpWord\Settings::setPdfRenderer(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));

        // Save the PhpWord document as PDF
        $phpWord->save($pdfFilePath, 'PDF');

        // Return the PDF file as a response
        return response()->file($pdfFilePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"',
        ]);
    }







    public function showResult(Request $request)
    {
        $data = $request->query('data');
        $result = $data;
        return view('result', ['result' => $result]);
    }
}
