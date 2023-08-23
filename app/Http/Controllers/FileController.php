<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Shared\Converter;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Alert;
use Illuminate\Support\Facades\Session;
use ValueError;
use Illuminate\Support\Facades\DB; // Import the DB facade



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
            Alert::success('Success', 'File sent successfully.')->autoClose(3000);
        } else {
            // Handle the case where no signature ID is found for the logged-in user
            return redirect()->back()->with('error', 'Signature not found for the logged-in user.');
        }

        // Generate the QR code from the shuffled sign_files data
        // $qrCode = QrCode::size(200)->generate($shuffledSignFiles);

        // Optionally, you can store the QR code image and associate it with the file record
        // You can save the QR code image using Storage facade or any image manipulation library

        // Redirect to the dashboard or the appropriate view
        return redirect()->back()->with('success', 'File sent successfully.');
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
            case 'decrypted':
                $filePath = storage_path('app/decrypted_files/' . $file->decrypted_file_name);
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

        // Set the success message for the receiver if there are new files received
        if ($receivedFiles->isNotEmpty()) {
            Session::flash('received', 'You have received a new file.');

            // Store the timestamp of the latest received file in the session
            Session::put('latest_received_file_timestamp', time());
        }

        return view('received-files', compact('receivedFiles'));
    }



    // public function download($id)
    // {
    //     $file = File::findOrFail($id);

    //     // Ensure that the logged-in user is the receiver of the file
    //     if (auth()->id() !== $file->receiver_id) {
    //         return redirect()->back()->with('error', 'Unauthorized access.');
    //     }

    //     // Construct the file path
    //     $filePath = storage_path('app/files/' . $file->unique_file_name);
    //     $fileName = $file->original_file_name;

    //     // Check if the file exists
    //     if (!file_exists($filePath)) {
    //         return redirect()->back()->with('error', 'File not found.');
    //     }

    //     // Define the headers for the download response
    //     $headers = [
    //         'Content-Type' => 'application/octet-stream',
    //         'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    //     ];

    //     // Return the file response for download
    //     return response()->download($filePath, $fileName, $headers);
    // }

    public function addSignature(File $file)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();

            // Sign the file using RSA
            $this->signFileWithRSA($file);

            // Add the QR code to the document
            $this->addQRCodeToDocument($file);

            // Commit the transaction if everything is successful
            DB::commit();

            return redirect()->back()->with('success', 'Signature added successfully.');
        } catch (\ValueError $ve) {
            // Rollback the transaction and reset status to "uploaded"
            DB::rollBack();
            $this->resetStatusToUploaded($file);

            return redirect()->back()->with('error', 'A ValueError occurred while processing the signature. Status changed to "uploaded".');
        } catch (\Exception $e) {
            // Rollback the transaction if any other exception occurs
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred while adding the signature.');
        }
    }

    private function resetStatusToUploaded(File $file)
    {
        $file->status = 'uploaded';
        $file->save();
    }

    private function signFileWithRSA(File $file)
    {
        // Load the private key
        $privateKey = openssl_pkey_get_private(file_get_contents(storage_path('app/key/private.key')));

        // Retrieve the existing file path
        $filePath = storage_path('app/files/' . $file->unique_file_name);

        // Read the file content
        $fileContent = Storage::get($filePath);

        // Generate the digital signature
        openssl_sign($fileContent, $signature, $privateKey);

        // Base64 encode the signature
        $encodedSignature = base64_encode($signature);

        // Update the file model with the signature and public key
        $file->signature = $encodedSignature;
        $file->public_key = file_get_contents(storage_path('app/key/public.key'));
        $file->save();
    }

    private function addQRCodeToDocument(File $file)
    {
        $tempDirectory = 'signed_files';

        // Retrieve the existing file path
        $filePath = storage_path('app/files/' . $file->unique_file_name);

        // Retrieve the sign_files from the database
        $signFiles = $file->sign_files;

        $file->status = 'signed';
        $file->unique_file_name = time() . '_signed_' . $file->original_file_name;

        $file->save();

        // Load the existing document using TemplateProcessor
        $templateProcessor = new TemplateProcessor($filePath);

        // Generate the token by concatenating the sign_files and file ID
        $token = $signFiles . $file->id;

        // Encrypt the token
        $encryptedToken = Crypt::encrypt($token);

        // Generate the QR code content
        $qrCodeContent = route('file-integrity-check', ['file' => $file, 'token' => $encryptedToken]);

        // Create an instance of the image renderer
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );

        // Create an instance of the QR code writer
        $writer = new Writer($renderer);

        // Generate the QR code image
        $qrCodeImage = $writer->writeString($qrCodeContent);

        // Create the signed_files directory if it doesn't exist
        $signedFilesDirectory = storage_path('app/' . $tempDirectory);
        FileFacade::makeDirectory($signedFilesDirectory, 0755, true, true);

        // Save the QR code image to a temporary file
        $qrCodePath = $signedFilesDirectory . '/qr_code.png';
        file_put_contents($qrCodePath, $qrCodeImage);

        // Calculate the size of the QR code image in the Word document
        $imageWidth = Converter::cmToPixel(4); // Adjust the width as needed
        $imageHeight = Converter::cmToPixel(4); // Adjust the height as needed

        // Find and replace the placeholder with the image in the document
        $templateProcessor->setImageValue('qr_code', $qrCodePath, $imageWidth, $imageHeight);

        // Remove the sign_files content from the document
        $templateProcessor->setValue('sign_files', '');

        // Save the modified document to a specific directory
        $modifiedFilePath = $signedFilesDirectory . '/' . $file->unique_file_name;
        $templateProcessor->saveAs($modifiedFilePath);
    }



    public function fileIntegrityCheck($fileId)
    {
        // Retrieve the file based on the provided file ID
        $file = File::with(['sender', 'receiver', 'signatures.user'])->findOrFail($fileId);

        // Load the public key
        $publicKey = openssl_pkey_get_public(file_get_contents(storage_path('app/key/public.key')));

        // Retrieve the existing file path
        $filePath = storage_path('app/files/' . $file->unique_file_name);

        // Read the file content
        $fileContent = Storage::get($filePath);

        // Base64 decode the stored signature
        $decodedSignature = base64_decode($file->signature);

        // Verify the signature
        $isVerified = openssl_verify($fileContent, $decodedSignature, $publicKey);

        // Get the name of the user performing the verification
        $verifierName = Auth::user()->name;

        // Get the current timestamp
        $verificationTimestamp = Carbon::now();

        // Return the verification result view with the verification status and additional information
        return view('file-integrity-check', [
            'signature' => $file->signature,
            'isVerified' => $isVerified,
            'verifierName' => $verifierName,
            'verificationTimestamp' => $verificationTimestamp,
            'decodedSignature' => $decodedSignature,
            'file' => $file,
        ]);
    }
}
