<!-- resources/views/create-signature.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Signatures') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">{{ __('Create Signature') }}</h1>

                    @if ($errors->any())
                        <div class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-md p-4 mb-6">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('signatures.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label for="signature_image" class="block text-gray-800 dark:text-gray-200 font-medium mb-2">{{ __('Signature Image') }}</label>
                            <input type="file" name="signature_image" id="signature_image" class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-700 rounded-md py-2 px-4 focus:outline-none focus:ring focus:border-blue-500">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold text-sm uppercase tracking-widest rounded-md focus:outline-none focus:shadow-outline focus:ring focus:ring-blue-200 active:bg-blue-700 transition ease-in-out duration-150">
                                {{ __('Upload Signature') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
