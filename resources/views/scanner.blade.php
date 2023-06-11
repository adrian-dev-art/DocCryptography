<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QR Code Scanner') }}
        </h2>
    </x-slot>

    <form action="{{ route('scanQRCode') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="qrcode" accept="image/*">
        <button type="submit">Scan QR Code</button>
    </form>

    
    {{-- <div id="qr-reader" style="width: 600px"></div>
    <div id="success-message"></div>
<div id="error-message"></div>



    {{-- <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
          console.log(`Code scanned = ${decodedText}`, decodedResult);
          // Handle the scanned QR code here
          
          // Show success message
          const successMessage = document.getElementById('success-message');
          successMessage.textContent = `Code scanned successfully: ${decodedText}`;
      
          // Clear any previous error messages
          const errorMessage = document.getElementById('error-message');
          errorMessage.textContent = '';
      
          // Display the scanned data on the console
          console.log('Scanned data:', decodedResult);
        }
      
        function onScanFailure(error) {
          console.error('QR code scan failed:', error);
      
          // Show error message
          const errorMessage = document.getElementById('error-message');
          errorMessage.textContent = 'QR code scan failed. Please try again.';
      
          // Clear any previous success message
          const successMessage = document.getElementById('success-message');
          successMessage.textContent = '';
        }
      
        var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
      </script>
       --}} --}}
      
</x-app-layout>
