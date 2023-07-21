<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Send File') }}
        </h2>
    </x-slot>
    

    <div class="flex justify-center items-center py-8">
        <div class="w-full max-w-7xl px-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                <div class="p-6 w-full max-w-7xl mx-auto">
                    <form action="{{ route('store-file') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label for="file" class="block text-gray-800 dark:text-gray-200 font-bold mb-2">
                                Choose a file:
                            </label>
                            <input type="file" name="file" id="file"
                                class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-700 rounded-md py-2 px-4 focus:outline-none focus:ring focus:border-purple-500">
                        </div>

                        <div class="mb-6">
                            <label for="receiver_id" class="block text-gray-800 dark:text-gray-200 font-bold mb-2">
                                Select a receiver:
                            </label>
                            <select name="receiver" id="receiver" class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-700 rounded-md py-2 px-4 focus:outline-none focus:ring focus:border-purple-500">
                                <option value="">-- Select Receiver --</option>
                                @foreach ($receivers as $receiver)
                                    <option value="{{ $receiver->id }}">{{ $receiver->name }}</option>
                                @endforeach
                            </select>
                            
                        </div>

                        <button type="submit"
                            class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Send
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="flex justify-center items-center py-6">
        <div class="w-full max-w-7xl px-6">
            <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 text-center">Sent Files
                        History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-purple-500 dark:bg-purple-700 text-white dark:text-gray-200">
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        File Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        File Size
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        Receiver
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        Time
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($sendedFiles as $sendedFile)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <a href="{{ route('download-file', $sendedFile->id) }}"
                                                class="text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-600 transition-colors duration-200">
                                                {{ $sendedFile->original_file_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{ $sendedFile->file_size }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{ $sendedFile->status }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{ $sendedFile->receiver->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{ $sendedFile->created_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($sendedFile->status === 'encrypted')
                                                <a href="{{ route('decrypt-file', $sendedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200">
                                                    Decrypt Now
                                                </a>
                                            @else
                                                <a href="{{ route('encrypt-file', $sendedFile->id) }}"
                                                    class="text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-600 transition-colors duration-200">
                                                    Encrypt
                                                </a>
                                            @endif
                                            @if ($sendedFile->status === 'uploaded')
                                                <a href="{{ route('add-signature', $sendedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 ml-2">
                                                    Add Signature
                                                </a>
                                            @else
                                                <a href="{{ route('download-file', $sendedFile->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 ml-2">
                                                    Download
                                                </a>
                                            @endif
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
