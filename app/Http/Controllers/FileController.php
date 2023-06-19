<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Barryvdh\DomPDF\PDF;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\Settings;




class FileController extends Controller
{
    public function showSendFileForm()
    {
        $receivers = User::where('id', '!=', auth()->id())->get();
        $sendedFiles = File::where('sender_id', auth()->id())->latest()->get();

        return view('send-file', compact('receivers', 'sendedFiles'));
    }

    public function storeFile(Request $request)
    {
        // Validate the form inputs
        $request->validate([
            'file' => 'required|file',
            'receiver' => 'required|exists:users,id',
        ]);

        // Get the uploaded file
        $uploadedFile = $request->file('file');

        // Generate a unique file name
        $fileName = time() . '_' . $uploadedFile->getClientOriginalName();

        // Store the file in storage or cloud storage service
        $filePath = $uploadedFile->storeAs('files', $fileName);

        // Get the absolute file path
        $absoluteFilePath = storage_path('app/' . $filePath);

        // Create a new file record
        $file = new File();
        $file->original_file_name = $uploadedFile->getClientOriginalName();
        $file->unique_file_name = $fileName;
        $file->file_size = $uploadedFile->getSize();
        $file->status = 'uploaded';
        $file->sender_id = Auth::id();
        $file->receiver_id = $request->input('receiver');

        // Retrieve the signature ID based on the logged-in user
        $signatureId = Signature::where('user_id', Auth::id())->value('id');

        // Check if a signature ID is found
        if ($signatureId) {
            // Convert the IDs to strings and combine them
            $signFiles = strval($signatureId) . strval($file->sender_id) . strval($file->receiver_id);


            // Split the sign_files into individual characters
            $signFilesCharacters = str_split($signFiles);

            // Shuffle the characters
            shuffle($signFilesCharacters);

            // Combine the shuffled characters back into a string
            $shuffledSignFiles = implode('', $signFilesCharacters);

            $file->sign_files = $shuffledSignFiles;

            $file->save();
        } else {
            // Handle the case where no signature ID is found for the logged-in user
            return redirect()->back()->with('error', 'Signature not found for the logged-in user.');
        }

        // Generate the QR code from the shuffled sign_files data
        // $qrCode = QrCode::size(200)->generate($shuffledSignFiles);

        // Optionally, you can store the QR code image and associate it with the file record
        // You can save the QR code image using Storage facade or any image manipulation library

        // Redirect to the dashboard or the appropriate view
        return redirect()->route('dashboard')->with('success', 'File sent successfully.');
    }


    public function encrypt($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is the sender of the file
        if (auth()->id() !== $file->sender_id && auth()->id() !== $file->receiver_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Determine the file path based on the file status
        switch ($file->status) {
            case 'uploaded':
                $filePath = storage_path('app/files/' . $file->unique_file_name);
                break;
            case 'signed':
                $filePath = storage_path('app/signed_files/' . $file->unique_file_name);
                break;
            default:
                return redirect()->back()->with('error', 'File not found for encryption.');
        }

        // Encrypt the file
        $encryptedFilePath = $this->encryptFile($filePath, $file->original_file_name);

        // Update the file status and encrypted file name
        $file->status = 'encrypted';
        $file->encrypted_file_name = pathinfo($encryptedFilePath, PATHINFO_BASENAME);
        $file->save();

        return redirect()->back()->with('success', 'File encrypted successfully.');
    }

    public function decrypt($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is either the sender or receiver of the file
        if (auth()->id() !== $file->sender_id && auth()->id() !== $file->receiver_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Determine the file path based on the file status
        switch ($file->status) {
            case 'encrypted':
                $filePath = storage_path('app/encrypted_files/' . $file->encrypted_file_name);
                break;
            case 'signed':
                $filePath = storage_path('app/signed_files/' . $file->unique_file_name);
                break;
            default:
                return redirect()->back()->with('error', 'File not found for decryption.');
        }

        // Decrypt the file
        $decryptedFilePath = $this->decryptFile($filePath, $file->original_file_name);

        // Update the file status and decrypted file name
        $file->status = 'decrypted';
        $file->decrypted_file_name = pathinfo($decryptedFilePath, PATHINFO_BASENAME);
        $file->save();

        // Optionally, you can perform additional actions with the decrypted file.

        return redirect()->back()->with('success', 'File decrypted successfully.');
    }

    private function encryptFile($filePath, $originalFileName)
    {
        // Generate a unique name for the encrypted file
        $encryptedFileName = time() . '_' . $originalFileName;

        // Define the path for the encrypted file
        $encryptedFilePath = 'encrypted_files/' . $encryptedFileName;

        // Read the contents of the original file using the absolute file path
        $fileContents = file_get_contents($filePath);

        // Generate a 16-byte IV
        $iv = openssl_random_pseudo_bytes(16);

        // Encrypt the file contents using AES-256 encryption in CBC mode
        $encryptedContents = openssl_encrypt($fileContents, 'AES-256-CBC', 'encryption_key', OPENSSL_RAW_DATA, $iv);

        // Prepend the IV to the encrypted contents
        $encryptedData = $iv . $encryptedContents;

        // Save the encrypted contents to the encrypted file
        Storage::put($encryptedFilePath, $encryptedData);

        // Return the path of the encrypted file
        return $encryptedFilePath;
    }

    private function decryptFile($filePath, $originalFileName)
    {
        // Define the path for the decrypted file
        $decryptedFileName = time() . '_' . $originalFileName;
        $decryptedFilePath = 'decrypted_files/' . $decryptedFileName;

        // Read the contents of the encrypted file using the absolute file path
        $encryptedData = file_get_contents($filePath);

        // Extract the IV from the encrypted data (first 16 bytes)
        $iv = substr($encryptedData, 0, 16);

        // Extract the encrypted contents from the remaining data
        $encryptedContents = substr($encryptedData, 16);

        // Decrypt the file contents using AES-256 decryption in CBC mode
        $decryptedContents = openssl_decrypt($encryptedContents, 'AES-256-CBC', 'encryption_key', OPENSSL_RAW_DATA, $iv);

        // Save the decrypted contents to the decrypted file
        Storage::put($decryptedFilePath, $decryptedContents);

        // Return the path of the decrypted file
        return $decryptedFilePath;
    }


    public function downloadFile($id)
    {
        $file = File::findOrFail($id);

        // Determine the file path based on the file status
        switch ($file->status) {
            case 'encrypted':
                $filePath = storage_path('app/encrypted_files/' . $file->encrypted_file_name);
                $fileName = $file->encrypted_file_name;
                break;
            case 'decrypted':
                $filePath = storage_path('app/decrypted_files/' . $file->decrypted_file_name);
                $fileName = $file->decrypted_file_name;
                break;
            case 'signed':
                $filePath = storage_path('app/signed_files/' . $file->unique_file_name);
                $fileName = $file->unique_file_name;
                break;
            default:
                return redirect()->back()->with('error', 'File not found.');
        }

        // Check if the file exists
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Define the headers for the download response
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Return the file response for download
        return response()->download($filePath, $fileName, $headers);
    }


    public function showReceivedFiles()
    {
        $receivedFiles = File::where('receiver_id', auth()->id())->latest()->get();

        foreach ($receivedFiles as $receivedFile) {
            // Retrieve the encrypted sign_files data from the database
            $encryptedData = $receivedFile->sign_files;

            // Generate the QR code from the encrypted data
            $qrCode = QrCode::size(80)->generate($encryptedData);

            // Add the QR code to the $receivedFile object for use in the view
            $receivedFile->qrCode = $qrCode;
        }

        return view('received-files', compact('receivedFiles'));
    }




    public function download($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is the receiver of the file
        if (auth()->id() !== $file->receiver_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Construct the file path
        $filePath = storage_path('app/files/' . $file->unique_file_name);
        $fileName = $file->original_file_name;

        // Check if the file exists
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Define the headers for the download response
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Return the file response for download
        return response()->download($filePath, $fileName, $headers);
    }

    public function addSignature(File $file)
    {
        $tempDirectory = 'signed_files';

        // Retrieve the existing file path
        $filePath = storage_path('app/files/' . $file->unique_file_name);

        // Retrieve the sign_files from the database
        $signFiles = $file->sign_files;

        $file->status = 'signed';
        $file->save();

        // Load the existing document
        $phpWord = IOFactory::load($filePath);

        // Get the active section or add a new section if none exists
        $section = $phpWord->addSection();

        // Create a paragraph and add the sign_files content
        $paragraph = $section->addText($signFiles);

        // Generate the token by concatenating the sign_files and file ID
        $token = $signFiles . $file->id;

        // Split the token into individual characters
        $tokenCharacters = str_split($token);

        // Shuffle the characters
        shuffle($tokenCharacters);

        // Combine the shuffled characters back into a string
        $shuffledToken = implode('', $tokenCharacters);


        // Generate the QR code PNG from the shuffled token

        $qrCodeContent = route('file-integrity-check', ['file' => $file, 'token' => $shuffledToken]);

        // encrypt the QR Content
        $encryptedQrCodeContent = Crypt::encrypt($qrCodeContent);

        $qrCodeImage = QrCode::format('png')->size(100)->generate($encryptedQrCodeContent);

        // Save the QR code image to a temporary file
        $qrCodePath = storage_path('app/' . $tempDirectory . '/qr_code.png');
        Storage::put($tempDirectory . '/qr_code.png', $qrCodeImage);

        // Load the QR code image using Intervention Image
        $qrCode = \Intervention\Image\Facades\Image::make($qrCodePath);

        // Calculate the size of the QR code image in the Word document
        $imageWidth = Converter::cmToPixel(2); // Adjust the width as needed
        $imageHeight = Converter::cmToPixel(2); // Adjust the height as needed

        // Resize the QR code image to the desired dimensions
        $qrCode->resize($imageWidth, $imageHeight);

        // Insert the QR code image into the Word document
        $section->addImage($qrCodePath, [
            'width' => $imageWidth,
            'height' => $imageHeight,
            'align' => 'right',
        ]);

        // Save the modified document to a specific directory
        $modifiedFilePath = storage_path('app/' . $tempDirectory . '/' . $file->unique_file_name);
        $phpWord->save($modifiedFilePath);

        // return redirect()->route('file-integrity-check', ['token' => $shuffledToken])->with('success', 'Signature added successfully.');
        return redirect()->back()->with('success', 'Signature added successfully.');
    }

    // public function fileIntegrityCheck($fileId)
    // {
    //     // Retrieve the file based on the provided file ID
    //     $file = File::with(['sender', 'receiver', 'signatures.user'])->findOrFail($fileId);

    //     // Retrieve the signature for the authenticated user by ID
    //     $userId = auth()->user()->id;
    //     $signature = Signature::where('user_id', $userId)->latest()->first();

    //     // Generate the QR code from the signature image
    //     $qrCode = QrCode::generate($signature->signature_image);

    //     // Return the file integrity check view with the file details
    //     return view('file-integrity-check', compact('file', 'qrCode'));
    // }

    // public function fileIntegrityCheck($fileId)
    // {
    //     // Retrieve the file based on the provided file ID
    //     $file = File::with(['sender', 'receiver', 'signatures.user'])->findOrFail($fileId);

    //     // Define the path to the DOCX file
    //     $docxFilePath = storage_path('app/files/' . $file->unique_file_name);

    //     // Generate a unique name for the PDF file
    //     $pdfFileName = time() . '_' . $file->original_file_name . '.pdf';

    //     // Define the path for the PDF file
    //     $pdfFilePath = storage_path('app/pdf_files/' . $pdfFileName);

    //     // Load the DOCX file using PhpWord
    //     $phpWord = \PhpOffice\PhpWord\IOFactory::load($docxFilePath);

    //     // Configure PhpWord to use the Dompdf PDF renderer
    //     Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
    //     Settings::setPdfRendererName('DomPDF');

    //     // Save the PhpWord document as PDF
    //     $phpWord->save($pdfFilePath, 'PDF');

    //     // Return the file and PDF file paths to the view
    //     return view('file-integrity-check', compact('file', 'pdfFileName', 'pdfFilePath'));
    // }


    public function fileIntegrityCheck($fileId)
    {
        // Retrieve the file based on the provided file ID
        $file = File::with(['sender', 'receiver', 'signatures.user'])->findOrFail($fileId);

        // Determine the directory based on the file status
        $directory = '';

        switch ($file->status) {
            case 'uploaded':
                $directory = 'files';
                break;
            case 'signed':
                $directory = 'signed_files';
                break;
            case 'encrypted':
                $directory = 'encrypted_files';
                break;
            case 'decrypted':
                $directory = 'decrypted_files';
                break;
            default:
                // Handle the case when the file status is unknown
                return redirect()->back()->with('error', 'Unknown file status.');
        }

        // Define the path to the file
        $filePath = storage_path('app/' . $directory . '/' . $file->unique_file_name);

        // Generate a unique name for the PDF file
        $pdfFileName = time() . '_' . $file->original_file_name . '.pdf';

        // Define the path for the PDF file
        $pdfFilePath = storage_path('app/pdf_files/' . $pdfFileName);

        // Load the file using PhpWord
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);

        // Configure PhpWord to use the Dompdf PDF renderer
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
        Settings::setPdfRendererName('DomPDF');

        // Save the PhpWord document as PDF
        $phpWord->save($pdfFilePath, 'PDF');

        // Return the PDF file as a response
        return response()->file($pdfFilePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"',
        ]);

        // Return the file and PDF file paths to the view
        // return view('file-integrity-check', compact('file', 'pdfFileName', 'pdfFilePath'));
    }
}
