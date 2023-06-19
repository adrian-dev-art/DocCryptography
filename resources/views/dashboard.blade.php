<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mail bg-white border border-purple-500">
                <div class="header py-4 px-6 relative bg-purple-500">
                    <i class="fas fa-envelope text-white bg-white p-3 rounded-full text-xl absolute right-6 top-6 border-2 border-purple-500 transition-transform hover:rotate-12"></i>
                </div>
                <div class="body pt-6 pb-8 px-6 border-b border-purple-500 border-l border-r">
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                        <div class="menu-card">
                            <a href="{{ route('send-file') }}" class="block">
                                <div class="hover:bg-indigo-500 hover:bg-opacity-10 rounded-lg p-4">
                                    <div class="mail-icon danger bg-red-500">
                                        <i class="fas fa-paper-plane text-white"></i>
                                    </div>
                                    <h2 class="mail-title text-purple-500 mt-4">Send File</h2>
                                    <p class="mail-description">Send File Safely with automatically Encrypted.</p>
                                </div>
                            </a>
                        </div>

                        <div class="menu-card">
                            <a href="{{ route('received-files') }}" class="block">
                                <div class="hover:bg-indigo-500 hover:bg-opacity-10 rounded-lg p-4">
                                    <div class="mail-icon success bg-green-500">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                    <h2 class="mail-title text-purple-500 mt-4">Received Files</h2>
                                    <p class="mail-description">View Received Files.</p>
                                </div>
                            </a>
                        </div>

                        <div class="menu-card">
                            <a href="{{ $signature ? route('signatures.show', $signature->id) : route('signatures.create') }}" class="block">
                                <div class="hover:bg-indigo-500 hover:bg-opacity-10 rounded-lg p-4">
                                    <div class="mail-icon warning bg-yellow-500">
                                        <i class="fas fa-pen text-white"></i>
                                    </div>
                                    <h2 class="mail-title text-purple-500 mt-4">Signature</h2>
                                    <p class="mail-description">See your Signature.</p>
                                </div>
                            </a>
                        </div>

                        <div class="menu-card">
                            <a href="{{ route('scan.form') }}" class="block">
                                <div class="hover:bg-indigo-500 hover:bg-opacity-10 rounded-lg p-4">
                                    <div class="mail-icon primary bg-indigo-500">
                                        <i class="fas fa-qrcode text-white"></i>
                                    </div>
                                    <h2 class="mail-title text-purple-500 mt-4">Scan QR Code</h2>
                                    <p class="mail-description">Scan or Upload QR Code.</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
