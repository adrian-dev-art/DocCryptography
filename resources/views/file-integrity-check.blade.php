<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('File Integrity Check') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="p-6">
                <h1 class="text-xl font-semibold">File Integrity Check</h1>
                <div class="mt-4">
                    <p><span class="font-semibold">File:</span> {{ $file->original_file_name }}</p>
                    <p><span class="font-semibold">Status:</span> {{ $file->status }}</p>
                    <p><span class="font-semibold">Sender Name:</span> {{ $file->sender->name }}</p>
                    <p><span class="font-semibold">Receiver Name:</span> {{ $file->receiver->name }}</p>
                    <p><span class="font-semibold">Sender Email:</span> {{ $file->sender->email }}</p>
                    <p><span class="font-semibold">Receiver Email:</span> {{ $file->receiver->email }}</p>
                    <p><span class="font-semibold">Sender QR Signature:</span> {{ $qrCode }}</p>

                    <!-- Add more details or customizations as needed -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
