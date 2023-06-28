<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('File Integrity Check') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto bg-white shadow-sm rounded-lg p-6">
            <h1 class="text-xl font-semibold">File Integrity Check</h1>
            <div class="mt-4">
                <p><span class="font-semibold">File:</span> {{ $file->original_file_name }}</p>
                <p><span class="font-semibold">Status:</span> {{ $file->status }}</p>
                <p><span class="font-semibold">Sender Name:</span> {{ $file->sender->name }}</p>
                <p><span class="font-semibold">Receiver Name:</span> {{ $file->receiver->name }}</p>
                <p><span class="font-semibold">Sender Email:</span> {{ $file->sender->email }}</p>
                <p><span class="font-semibold">Receiver Email:</span> {{ $file->receiver->email }}</p>
                <div class="mt-6">
                    @if ($file->status === 'encrypted')
                        <p>File is encrypted</p>
                    @else
                        <a href="{{ route('preview-pdf', ['fileId' => $file->id]) }}" target="_blank"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Open PDF
                        </a>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
