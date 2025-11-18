<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure URL Encryptor</title>

    <!-- Tailwind CDN (remove if already included globally) -->
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-xl bg-white shadow-xl rounded-2xl p-8">

        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            🔐 Secure Streaming URL Generator
        </h2>

        <!-- Form -->
        <form method="POST" action="/encrypt" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1 font-semibold">Enter Real Video URL</label>
                <input 
                    type="text" 
                    name="url"
                    placeholder="https://example.com/video.mp4"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-400"
                >
            </div>

            <button
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                Generate Secure URL
            </button>
        </form>

        @if(isset($secureUrl))
        <hr class="my-8">

        <div class="space-y-3">
            <h3 class="font-bold text-lg text-gray-800">Secure Stream URL</h3>

            <div class="flex gap-2">
                <input 
                    id="streamUrl" 
                    type="text" 
                    value="{{ $secureUrl }}" 
                    class="flex-1 px-4 py-2 border rounded-lg bg-gray-50"
                    readonly
                >
                <button 
                    onclick="copyText('streamUrl')" 
                    class="bg-gray-700 hover:bg-black text-white px-4 rounded-lg">
                    Copy
                </button>
            </div>

            <h3 class="font-bold text-lg text-gray-800 mt-4">Secure Download URL</h3>

            @php
                $token = basename($secureUrl);
                $downloadUrl = url('/download/' . $token);
            @endphp

            <div class="flex gap-2">
                <input 
                    id="downloadUrl"
                    type="text"
                    value="{{ $downloadUrl }}"
                    class="flex-1 px-4 py-2 border rounded-lg bg-gray-50"
                    readonly
                >
                <button 
                    onclick="copyText('downloadUrl')" 
                    class="bg-gray-700 hover:bg-black text-white px-4 rounded-lg">
                    Copy
                </button>
            </div>
        </div>
        @endif
    </div>

    <script>
        function copyText(id) {
            const copyField = document.getElementById(id);
            copyField.select();
            copyField.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyField.value);

            alert("Copied to clipboard!");
        }
    </script>

</body>
</html>
