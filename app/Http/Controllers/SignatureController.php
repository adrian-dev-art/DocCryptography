<?php

namespace App\Http\Controllers;

use App\Models\Signature;

use Illuminate\Http\Request;

use BaconQrCode\Facades\QrCode;


class SignatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('signatures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'signature_image' => 'required|image',
        ]);

        // Get the authenticated user's ID
        $userId = auth()->id();

        // Upload and store the signature image
        if ($request->hasFile('signature_image')) {
            $signatureImage = $request->file('signature_image');
            $signatureImagePath = $signatureImage->store('signature_images', 'public');
        }

        // /** @var \BaconQrCode\Response\Generator\GeneratorInterface $qrCode */
        // $qrCode = QrCode::format('png')->generate($signatureImagePath);

        // Create the signature record
        Signature::create([
            'user_id' => $userId,
            'signature_image' => $signatureImagePath,
        ]);

        // Redirect to the dashboard with a success message
        return redirect()->route('dashboard')->with('success', 'Signature created successfully.');
    }




    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Retrieve the signature for the authenticated user by ID
        $signature = Signature::where('user_id', $userId)->latest()->first();
    

        // // Generate the QR code from the signature image
        // $qrCode = QrCode::format('png')->generate($signature->signature_image);

        return view('signatures.show', compact('signature',));
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
