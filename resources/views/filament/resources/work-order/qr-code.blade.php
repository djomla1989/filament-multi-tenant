<div class="p-4 space-y-4">
    <div class="text-center">
        <h2 class="text-xl font-bold">{{ $workOrder->order_number }}</h2>
        <p class="text-gray-500">{{ $workOrder->title }}</p>
    </div>

    <div class="flex justify-center">
        <div class="p-4 border rounded-lg bg-white">
            {!! $qrCode !!}
        </div>
    </div>

    <div class="text-center">
        <p class="text-sm">Scan this QR code to track the order.</p>
        <p class="text-sm mb-2">Tracking URL:</p>
        <div class="flex items-center justify-center space-x-2">
            <input type="text" value="{{ $trackingUrl }}" class="border rounded px-2 py-1 text-xs w-full" readonly onclick="this.select()" />
            <button
                type="button"
                class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded"
                onclick="navigator.clipboard.writeText('{{ $trackingUrl }}').then(() => {
                    this.innerText = 'Copied!';
                    setTimeout(() => { this.innerText = 'Copy'; }, 2000);
                })"
            >
                Copy
            </button>
        </div>
    </div>
</div>
