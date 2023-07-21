<div class="container">
    @if ($isVerified === 1)
        <h3>Verification Status: <span style="color: green;">Signature is verified.</span></h3>
    @elseif ($isVerified === 0)
        <h3>Verification Status: <span style="color: red;">Signature verification failed.</span></h3>
    @else
        <h3>Verification Status: <span style="color: red;">An error occurred during signature verification.</span></h3>
    @endif

    <div>
        {{-- <h4>File Information:</h4>
            <p>File Name: {{ $file->original_file_name }}</p>
            <p>File Size: {{ $file->file_size }} bytes</p>
            <p>Sender Name: {{ $file->sender->name }}</p>
            <p>Receiver Name: {{ $file->receiver->name }}</p> --}}
    </div>

    @if ($isVerified === 1)
        <div>
            <h4>Digital Signature:</h4>
            <p>{{ $signature }}</p>
        </div>
    @endif
</div>
