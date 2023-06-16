<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Send File') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                <div class="p-6">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('store-file') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="file" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">
                                Choose a file:
                            </label>
                            <input type="file" name="file" id="file"
                                class="border border-gray-300 dark:border-gray-700 rounded-md py-2 px-3 focus:outline-none focus:ring focus:border-blue-500">
                            @error('file')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="receiver_id" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">
                                Select a receiver:
                            </label>
                            <select name="receiver" id="receiver"
                                class="border border-gray-300 dark:border-gray-700 rounded-md py-2 px-3 focus:outline-none focus:ring focus:border-blue-500">
                                <option value="">-- Select Receiver --</option>
                                @foreach ($receivers as $receiver)
                                    <option value="{{ $receiver->id }}">{{ $receiver->name }}</option>
                                @endforeach
                            </select>
                            @error('receiver_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- working properly encrypt data but not for decrypted --}}
    {{-- <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Sent Files History</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-600">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    File Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    File Size
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Receiver
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Time
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach ($sendedFiles as $sendedFile)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->original_file_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->file_size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->status }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->receiver->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->created_at }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($sendedFile->status === 'encrypted')
                                            <a href="{{ route('decrypt-file', $sendedFile->id) }}"
                                                class="text-blue-500 hover:text-blue-700">Decrypt</a>
                                        @else
                                            <a href="{{ route('encrypt-file', $sendedFile->id) }}"
                                                class="text-green-500 hover:text-green-700">Encrypt</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Sent Files History</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-600">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    File Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    File Size
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Receiver
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Time
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach ($sendedFiles as $sendedFile)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->original_file_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->file_size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->status }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->receiver->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $sendedFile->created_at }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($sendedFile->status === 'encrypted')
                                                <a href="{{ route('decrypt-file', $sendedFile->id) }}">Decrypt Now</a>
                                        @else
                                            <a href="{{ route('encrypt-file', $sendedFile->id) }}"
                                                class="text-green-500 hover:text-green-700">Encrypt</a>
                                        @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($sendedFile->status === 'uploaded' )
                                        <a href="{{ route('add-signature', $sendedFile->id) }}" target="_blank">Add
                                            Signature</a>
                                            @else
                                            <a href="#">Download</a>
                                        @endif
                                    </td>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
