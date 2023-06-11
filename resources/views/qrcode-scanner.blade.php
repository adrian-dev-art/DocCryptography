<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QR Code Scanner') }}
        </h2>
    </x-slot>

    <div class="container py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="scanner-container">
                        <video id="scanner-video"></video>
                </div>
                    <div id="scanned-content"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js"></script>
    <script>
        const scannerContainer = document.getElementById('scanner-container');
        const videoElement = document.getElementById('scanner-video');
        const scannedContentElement = document.getElementById('scanned-content');

        function hasGetUserMedia() {
            return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        }

        function initQRCodeScanner() {
            if (hasGetUserMedia()) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then((stream) => {
                        videoElement.srcObject = stream;
                        scanQRCode();
                    })
                    .catch((error) => {
                        console.error('Error accessing camera: ', error);
                    });
            } else {
                console.error('getUserMedia() is not supported by your browser.');
            }
        }

        function scanQRCode() {
            const canvasElement = document.createElement('canvas');
            const canvasContext = canvasElement.getContext('2d');

            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;

            canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

            const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
                scannedContentElement.innerText = code.data;
                window.location.href = '/received-file/' + encodeURIComponent(code.data);
            }

            requestAnimationFrame(scanQRCode);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initQRCodeScanner();
        });
    </script>
</x-app-layout>
