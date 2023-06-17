<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Scan QR Code with Camera</h2>
                    <div id="cameraSection">
                        <video id="cameraPreview" autoplay playsinline></video>
                        <p id="qrCodeResult" class="mt-4"></p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Upload and Scan QR Code</h2>
                    <form action="{{ route('scan') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <label for="qr_code" class="block font-medium text-gray-700 dark:text-gray-400">Choose
                                File</label>
                            <input type="file" id="qr_code" name="qr_code" accept="image/*" class="mt-1">
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-300">Upload
                                and Scan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script>
        // Check for camera support
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.log('Camera not supported.');
        } else {
            const videoElement = document.getElementById('cameraPreview');
            const qrCodeResultElement = document.getElementById('qrCodeResult');
            let videoStream = null;
            let scanning = true; // Flag variable to control scanning status

            // Function to start the camera preview
            function startCamera() {
                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(function(stream) {
                        videoElement.srcObject = stream;
                        videoStream = stream;
                        videoElement.addEventListener('loadedmetadata', scanQRCode);
                    })
                    .catch(function(error) {
                        console.log('Error accessing camera:', error);
                    });
            }

            // Function to scan for QR codes in the video frames
            function scanQRCode() {
                if (!scanning) return; // Stop scanning if flag is false

                const videoWidth = videoElement.videoWidth;
                const videoHeight = videoElement.videoHeight;

                if (videoWidth && videoHeight) {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = videoWidth;
                    canvas.height = videoHeight;
                    context.drawImage(videoElement, 0, 0, videoWidth, videoHeight);
                    const imageData = context.getImageData(0, 0, videoWidth, videoHeight);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert',
                    });

                    if (code) {
                        scanning = false; // Set flag to false once QR code is detected

                        if (isValidURL(code.data)) {
                            window.location.href = code
                            .data; // Redirect to the result page if the scanned QR code is a link
                        } else {
                            qrCodeResultElement.innerText = code
                            .data; // Display the text if the scanned QR code is not a link
                        }
                    }
                }

                // Request the next frame
                requestAnimationFrame(scanQRCode);
            }

            // Function to check if a string is a valid URL
            function isValidURL(str) {
                try {
                    new URL(str);
                    return true;
                } catch (_) {
                    return false;
                }
            }

            // Start the camera preview
            startCamera();
        }
    </script>

</x-app-layout>
