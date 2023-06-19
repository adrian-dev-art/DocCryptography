<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Received Files') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Received Files</h3>
                    @if (session('error'))
                        <div class="text-red-500 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-500 dark:bg-blue-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        File Name
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        File Size
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        Sender
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        Time
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        Decrypt
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        Download
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-white dark:text-gray-200 uppercase tracking-wider">
                                        QR Code
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @foreach ($receivedFiles as $receivedFile)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $receivedFile->original_file_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $receivedFile->file_size }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $receivedFile->status }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $receivedFile->sender->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $receivedFile->created_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($receivedFile->status === 'encrypted')
                                                <a href="{{ route('decrypt-file', $receivedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200">
                                                    Decrypt
                                                </a>
                                            @else
                                                <a href="{{ route('encrypt-file', $receivedFile->id) }}"
                                                    class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-600 transition-colors duration-200">
                                                    Encrypt Now
                                                </a>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('download-file', $receivedFile->id) }}"
                                                class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200">
                                                Download Now
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="bg-white p-2 rounded-lg">
                                                {!! $receivedFile->qrCode !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
