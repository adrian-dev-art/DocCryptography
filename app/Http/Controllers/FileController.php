<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

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

        // Encrypt the file with AES-256
        $encryptedFilePath = $this->encryptFile($absoluteFilePath);

        // Create a new file record
        $file = new File();
        $file->original_file_name = $uploadedFile->getClientOriginalName(); // Updated property name
        $file->encrypted_file_name = pathinfo($encryptedFilePath, PATHINFO_BASENAME);
        $file->file_size = $uploadedFile->getSize();
        $file->status = 'encrypted';
        $file->sender_id = Auth::id();
        $file->receiver_id = $request->input('receiver');
        $file->save();


        // Optionally, you can redirect to a success page or show a success message
        return redirect()->route('dashboard')->with('success', 'File sent successfully.');
    }

    private function encryptFile($filePath)
    {
        // Generate a unique name for the encrypted file
        $encryptedFileName = 'encrypted_' . time() . '_' . pathinfo($filePath, PATHINFO_BASENAME);

        // Define the path for the encrypted file
        $encryptedFilePath = str_replace('\\', '/', storage_path('app/encrypted_files/' . $encryptedFileName));

        // Remove any double forward slashes in the file path
        $encryptedFilePath = preg_replace('#/+#', '/', $encryptedFilePath);

        // dd(pathinfo($filePath, PATHINFO_BASENAME));
        // Create the directory if it doesn't exist
        if (!file_exists(dirname($encryptedFilePath))) {
            mkdir(dirname($encryptedFilePath), 0777, true);
        }

        // Read the contents of the original file
        $fileContents = file_get_contents($filePath);
        // Generate a 16-byte IV
        $iv = openssl_random_pseudo_bytes(16);

        // Encrypt the file contents using AES-256 encryption
        $encryptedContents = openssl_encrypt($fileContents, 'AES-256-CBC', 'encryption_key', 0, $iv);

        // Prepend the IV to the encrypted contents and base64 encode the result
        $encryptedData = base64_encode($iv . $encryptedContents);

        // Save the encrypted contents to the encrypted file
        file_put_contents($encryptedFilePath, $encryptedData);

        // Return the path of the encrypted file
        return str_replace('\\', '/', $encryptedFilePath);
    }



    public function showReceivedFiles()
    {
        $receivedFiles = File::where('receiver_id', auth()->id())->get();

        return view('received-files', compact('receivedFiles'));
    }

    public function download($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is the receiver of the file
        if (auth()->id() !== $file->receiver_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if ($file->status === 'encrypted') {
            $filePath = storage_path('app/encrypted_files/' . $file->encrypted_file_name);
            $fileName = $file->original_file_name;
        } elseif ($file->status === 'decrypted') {
            $filePath = storage_path('app/decrypted_files/' . $file->decrypted_file_name);
            $fileName = $file->original_file_name;
        } else {
            return redirect()->back()->with('error', 'Invalid file status.');
        }

        if (empty($filePath) || !file_exists($filePath)) {
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


    public function decrypt($id)
    {
        $file = File::findOrFail($id);

        // Ensure that the logged-in user is the receiver of the file
        if (auth()->id() !== $file->receiver_id) {
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
