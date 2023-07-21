<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('File Integrity Check') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                    <h1 class="text-3xl font-semibold text-white">File Integrity Check</h1>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">File Details</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p><span class="font-semibold text-gray-700">File:</span> {{ $file->original_file_name }}</p>
                                    <p><span class="font-semibold text-gray-700">Status:</span> {{ $file->status }}</p>
                                    <p><span class="font-semibold text-gray-700">Sender Name:</span> {{ $file->sender->name }}</p>
                                    <p><span class="font-semibold text-gray-700">Receiver Name:</span> {{ $file->receiver->name }}</p>
                                </div>
                                <div>
                                    <p><span class="font-semibold text-gray-700">Sender Email:</span> {{ $file->sender->email }}</p>
                                    <p><span class="font-semibold text-gray-700">Receiver Email:</span> {{ $file->receiver->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if ($file->status === 'encrypted')
                                <p class="text-center text-lg font-semibold text-blue-600">File is encrypted</p>
                            @else
                                <a href="{{ route('preview-pdf', ['fileId' => $file->id]) }}" target="_blank"
                                    class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md text-center">
                                    Open PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($file->status === 'signed')
                    <div class="border-t border-gray-200 px-6 py-4">
                        <h2 class="text-2xl font-semibold text-gray-800">Digital Signature Verification</h2>
                        <div class="border rounded-lg p-4 mt-4">
                            <p class="font-semibold text-gray-700">Signature:</p>
                            <p class="whitespace-pre-wrap break-all">{{ $file->signature }}</p>
                        </div>
                        <p class="mt-2"><span class="font-semibold text-gray-700">Verified:</span> {{ $isVerified ? 'Yes' : 'No' }}</p>
                        <p><span class="font-semibold text-gray-700">Verifier Name:</span> {{ $verifierName }}</p>
                        <p><span class="font-semibold text-gray-700">Verification Timestamp:</span> {{ $verificationTimestamp }}</p>

                        @if ($isVerified)
                            <p class="text-green-600 font-semibold mt-4">The digital signature is valid.</p>
                        @else
                            <p class="text-red-600 font-semibold mt-4">The digital signature is invalid.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
