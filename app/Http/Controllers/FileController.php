<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManagerStatic as Image;

class FileController extends Controller
{
    public function showSendFileForm()
    {
        $receivers = User::where('id', '!=', auth()->id())->get();
        $sendedFiles = File::where('sender_id', auth()->id())->get();

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

        // Generate the sign files data by combining the sign_id, user_id, and file_id
        $signFiles = $file->signature_id . $file->sender_id . $file->id;

        // Convert the sign files data to a string
        $signFilesString = (string) $signFiles;

        // Encrypt the sign files data
        $encryptedSignFiles = Crypt::encrypt($signFilesString);
        $file->sign_files = $encryptedSignFiles;

        $file->save();

        // Generate the QR code from the encrypted sign files data
        $qrCode = QrCode::size(200)->generate($encryptedSignFiles);

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

        // Check if the file exists in the decrypted files directory
        $decryptedFilePath = 'decrypted_files/' . $file->decrypted_file_name;
        if (Storage::exists($decryptedFilePath)) {
            $filePath = storage_path('app/' . $decryptedFilePath);
        } else {
            // Fall back to the uploaded files directory
            $uploadedFilePath = 'files/' . $file->unique_file_name;
            if (Storage::exists($uploadedFilePath)) {
                $filePath = storage_path('app/' . $uploadedFilePath);
            } else {
                return redirect()->back()->with('error', 'File not found for encryption.');
            }
        }

        // Encrypt the file
        $encryptedFilePath = $this->encryptFile($filePath);

        // Update the file status and encrypted file name
        $file->status = 'encrypted';
        $file->encrypted_file_name = pathinfo($encryptedFilePath, PATHINFO_BASENAME);
        $file->save();

        return redirect()->back()->with('success', 'File encrypted successfully.');
    }

    private function encryptFile($filePath)
    {
        // Generate a unique name for the encrypted file
        $encryptedFileName = time() . '_' . pathinfo($filePath, PATHINFO_BASENAME);

        // Define the path for the encrypted file
        $encryptedFilePath = storage_path('app/encrypted_files/' . $encryptedFileName);

        // Create the directory if it doesn't exist
        if (!file_exists(dirname($encryptedFilePath))) {
            mkdir(dirname($encryptedFilePath), 0777, true);
        }

        // Read the contents of the original file
        $fileContents = Storage::get($filePath);

        // Generate a 16-byte IV
        $iv = openssl_random_pseudo_bytes(16);

        // Encrypt the file contents using AES-256 encryption
        $encryptedContents = openssl_encrypt($fileContents, 'AES-256-CBC', 'encryption_key', 0, $iv);

        // Prepend the IV to the encrypted contents
        $encryptedData = $iv . $encryptedContents;

        // Save the encrypted contents to the encrypted file
        file_put_contents($encryptedFilePath, $encryptedData);

        // Return the path of the encrypted file
        return str_replace('\\', '/', $encryptedFilePath);
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
        $receivedFiles = File::where('receiver_id', auth()->id())->get();

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

        // Generate the QR code PNG
        $qrCodeContent = $signFiles;
        $qrCodeImage = QrCode::format('png')->size(100)->generate($qrCodeContent);

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

        return redirect()->back()->with('success', 'Signature added successfully.');
    }

    public function decrypt($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is the receiver of the file
        if (auth()->id() !== $file->sender_id && auth()->id() !== $file->receiver_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }


        // Decrypt the file
        $decryptedFilePath = $this->decryptFile($file->encrypted_file_name);

        // Update the file status and decrypted name
        $file->status = 'decrypted';
        $file->decrypted_file_name = pathinfo($decryptedFilePath, PATHINFO_BASENAME);
        $file->save();

        // Optionally, you can perform additional actions with the decrypted file.

        return redirect()->back()->with('success', 'File decrypted successfully.');
    }

    private function decryptFile($encryptedFileName)
    {

        // Define the path for the encrypted file
        $encryptedFilePath = storage_path('app/encrypted_files/' . $encryptedFileName);

        // Retrieve the encrypted file contents
        $encryptedData = file_get_contents($encryptedFilePath);

        // Decode the base64-encoded encrypted data
        $encryptedData = base64_decode($encryptedData);

        // Extract the IV from the encrypted data (first 16 bytes)
        $iv = substr($encryptedData, 0, 16);

        // Extract the encrypted contents from the remaining data
        $encryptedContents = substr($encryptedData, 16);

        // Decrypt the file contents using AES-256 decryption
        $decryptedContents = openssl_decrypt($encryptedContents, 'AES-256-CBC', 'encryption_key', 0, $iv);

        // Generate a unique decrypted file name based on the original file name
        $decryptedFileName = 'decrypted_' . time() . '_' . $encryptedFileName;

        // Define the path for the decrypted file
        $decryptedFilePath = storage_path('app/decrypted_files/' . $decryptedFileName);

        // Create the directory if it doesn't exist
        if (!file_exists(dirname($decryptedFilePath))) {
            mkdir(dirname($decryptedFilePath), 0777, true);
        }

        // Save the decrypted contents to the decrypted file
        file_put_contents($decryptedFilePath, $decryptedContents);

        // Return the path of the decrypted file
        return str_replace('\\', '/', $decryptedFilePath);
    }
}
