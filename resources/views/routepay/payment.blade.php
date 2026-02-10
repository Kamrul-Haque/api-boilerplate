<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Pay Payment Gateway</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-2">Route Pay</h1>
        <p class="text-gray-500 text-center mb-6">Secure Payment Gateway</p>

        <form action="{{ config('routepay.base_url') . 'payment/stream_seed' }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="access_token" value="{{ $access_token }}">
            <input type="hidden" name="pay_mode" value="0">

            <div>
                <label for="amount" class="block text-gray-700 font-medium mb-2">
                    Enter Amount
                </label>
                <input type="number" id="amount" name="amount" required placeholder="e.g. 1000"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <button type="submit"
                class="w-full bg-[#07C6CD] hover:bg-[#07C6CD] text-white font-semibold py-2.5 rounded-lg shadow-md transition transform hover:scale-[1.02]">
                Pay with Route Pay
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            Powered by <span class="font-semibold text-[#07C6CD]">RouteCode</span>
        </p>
    </div>
</body>

</html>
