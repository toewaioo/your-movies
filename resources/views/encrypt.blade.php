<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure URL Encryptor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3/dist/tailwind.min.css">

    <script>
        function copyLink() {
            const text = document.getElementById("output");
            navigator.clipboard.writeText(text.value);
            alert("Copied!");
        }
    </script>
</head>
<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-16 bg-white shadow-lg rounded-xl p-6">
    <h2 class="text-2xl font-bold text-center mb-4 text-blue-600">
        🔐 Secure Download Link Generator
    </h2>

    <form method="POST" action="{{ route('encrypt.process') }}">
        @csrf

        <!-- KEEP THE KEY SECURE -->
        <input type="hidden" name="key" value="{{ $pageKey }}">

        <label class="block mb-2 text-gray-700 font-semibold">Enter Original URL:</label>
        <input type="text" name="url" required
               class="w-full border rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500"
               placeholder="https://example.com/file.mp4">

        <button class="mt-4 w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700">
            Generate Secure Link
        </button>
    </form>

    @isset($downloadUrl)
        <div class="mt-6 p-4 bg-gray-50 rounded border">
            <label class="block mb-2 text-gray-700 font-semibold">Encrypted Download URL:</label>
            <input id="output" type="text" readonly value="{{ $downloadUrl }}"
                   class="w-full border rounded p-2">

            <button onclick="copyLink()"
                    class="mt-2 w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">
                Copy Link
            </button>
        </div>
    @endisset

</div>

</body>
</html>
