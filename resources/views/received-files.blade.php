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
                                        Action
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
                                            {{-- @if ($receivedFile->status === 'decrypted')
                                            <a href="{{ route('encrypt-file', $receivedFile->id) }}"
                                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600 transition-colors duration-200">
                                                Encrypt
                                            </a>
                                            @elseif ($receivedFile->status === 'encrypted')
                                                <a href="{{ route('decrypt-file', $receivedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200">
                                                    Decrypt
                                                </a>
                                            @elseif ($receivedFile->status === 'uploaded')
                                                <a href="{{ route('add-signature', $receivedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 ml-2">
                                                    Add Signature
                                                </a>
                                            @endif --}}

                                            @if ($receivedFile->status === 'uploaded')
                                                <a href="{{ route('add-signature', $receivedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 ml-2">
                                                    Add Signature
                                                </a>
                                            @elseif ($receivedFile->status === 'signed' || $receivedFile->status === 'decrypted')
                                                <a href="{{ route('encrypt-file', $receivedFile->id) }}"
                                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600 transition-colors duration-200">
                                                    Encrypt
                                                </a>
                                            @elseif ($receivedFile->status === 'encrypted')
                                                <a href="{{ route('decrypt-file', $receivedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200">
                                                    Decrypt
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

    <!-- Add the "Stop Toast" button -->
    @if (session('received'))
        <button id="stopToastBtn" class="btn btn-danger" style="display: none;">Stop Toast</button>
    @endif

    <!-- Add the SweetAlert2 JS script at the end of your HTML body -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        // Function to show the toast
        function showToast(message) {
            Swal.fire({
                icon: 'success',
                title: 'New File Received!',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                onClose: function() {
                    // When the toast is closed, show the "Stop Toast" button
                    document.getElementById('stopToastBtn').style.display = 'block';
                }
            });
        }

        // Show the toast if there are new files received in the session
        @if (session('received'))
            showToast('{{ session('received') }}');
        @endif

        // Add an event listener to the "Stop Toast" button
        $('#stopToastBtn').on('click', function() {
            // Hide the button
            $(this).hide();
        });
    </script>

</x-app-layout>
