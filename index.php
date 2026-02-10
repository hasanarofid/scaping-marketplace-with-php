<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokopedia to Shopee Scraper</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('text-indigo-600', 'border-indigo-600');
                el.classList.add('text-gray-500', 'border-transparent');
            });
            
            document.getElementById(tab + '-content').classList.remove('hidden');
            document.getElementById(tab + '-btn').classList.add('text-indigo-600', 'border-indigo-600');
            document.getElementById(tab + '-btn').classList.remove('text-gray-500', 'border-transparent');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col items-center py-10">

    <div class="w-full max-w-4xl px-4">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Tokopedia to Shopee</h1>
            <p class="text-lg text-gray-500">Mass Product Scraper & Excel Generator</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden glass border border-gray-100">
            
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button id="manual-btn" onclick="switchTab('manual')" class="tab-btn w-1/2 py-4 text-center font-medium text-indigo-600 border-b-2 border-indigo-600 hover:text-indigo-800 transition">
                    Manual HTML (Recommended)
                </button>
                <button id="auto-btn" onclick="switchTab('auto')" class="tab-btn w-1/2 py-4 text-center font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 transition">
                    Automatic Scraper
                </button>
            </div>

            <form action="process.php" method="POST" class="p-8">
                
                <!-- Manual Tab (Default) -->
                <div id="manual-content" class="tab-content space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Paste HTML Source Code (Anti-Blocked)</label>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <strong>Cara Pakai:</strong> Buka Tokopedia -> Tekan <code>Ctrl+U</code> (View Source) -> <code>Ctrl+A</code> (Select All) -> <code>Ctrl+C</code> (Copy) -> Paste di sini.
                            </p>
                        </div>
                        <textarea name="manual_html" rows="10" class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm" placeholder="Paste kode HTML panjang di sini..."></textarea>
                    </div>
                </div>

                <!-- Automatic Tab -->
                <div id="auto-content" class="tab-content hidden space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tokopedia Product URLs</label>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> Fitur ini sering diblokir (Error 410) oleh Tokopedia jika pakai Shared Hosting. Gunakan tab <strong>Manual HTML</strong> jika gagal.
                            </p>
                        </div>
                        <textarea name="urls" rows="10" class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm" placeholder="https://www.tokopedia.com/shop/product-1&#10;https://www.tokopedia.com/shop/product-2"></textarea>
                    </div>
                </div>

                <!-- Action -->
                <div class="mt-8">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-lg hover:bg-indigo-700 transition transform hover:-translate-y-0.5 shadow-lg">
                        Processing & Download Shopee Excel
                    </button>
                </div>

            </form>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400">
            <p>Designed for Shared Hosting Environments. Results may vary based on Tokopedia UI changes.</p>
        </div>
    </div>

</body>
</html>
