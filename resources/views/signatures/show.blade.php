<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Signature Details') }}
        </h2>
    </x-slot>
    {{-- @dd($signature); --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-2xl font-bold mb-4">{{ __('Signature Details') }}</h1>

                    <div class="mb-4">
                        <label
                            class="block font-medium text-gray-700 dark:text-gray-300">{{ __('Signature Image') }}</label>
                        <img src="{{ asset('storage/' . $signature->signature_image) }}" alt="Signature Image">
                    </div>

                    <!-- Display other signature details here -->
                    {{-- <img src="{{ $qrCode }}" alt="QR Code"> --}}


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
