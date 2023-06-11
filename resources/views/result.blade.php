<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Scan Result</h2>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
                    <p class="text-gray-900 dark:text-white text-lg">Scanned QR Code:</p>
                    @if (filter_var($result, FILTER_VALIDATE_URL))
                        <a href="{{ $result }}" class="text-blue-500 hover:text-blue-700 break-all">{{ $result }}</a>
                    @else
                        <span class="break-all">{{ $result }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
