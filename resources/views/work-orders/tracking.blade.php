<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $workOrder->order_number }} - Order Tracking</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold text-gray-900">{{ $workOrder->organization->name }}</h1>
                </div>
            </div>
        </header>

        <main class="flex-grow">
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Order Tracking Content -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Order #{{ $workOrder->order_number }}</h2>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $workOrder->title }}</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $workOrder->currentStatus->name === 'Completed' ? 'green' : ($workOrder->currentStatus->name === 'In Progress' ? 'yellow' : 'gray') }}-100 text-{{ $workOrder->currentStatus->name === 'Completed' ? 'green' : ($workOrder->currentStatus->name === 'In Progress' ? 'yellow' : 'gray') }}-800">
                                {{ $workOrder->currentStatus->name }}
                            </span>
                        </div>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $workOrder->customer->name }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Category</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $workOrder->workCategory->name }}</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $workOrder->description }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $workOrder->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            @if($workOrder->estimated_completion_date)
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Estimated Completion</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $workOrder->estimated_completion_date->format('M d, Y H:i') }}</dd>
                            </div>
                            @endif

                            <!-- Order Details -->
                            @if($workOrder->details->count() > 0)
                            <div class="bg-white px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Order Details</h3>
                            </div>
                            @foreach($workOrder->details as $detail)
                            <div class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }} px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">{{ $detail->key }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $detail->value }}</dd>
                            </div>
                            @endforeach
                            @endif

                            <!-- Order History -->
                            <div class="bg-white px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Order History</h3>
                            </div>
                            <div class="bg-white px-4 py-5 sm:px-6">
                                <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    @foreach($workOrder->history as $historyItem)
                                    <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                        <div class="w-0 flex-1 flex items-center">
                                            <span class="flex-1 w-0 truncate">
                                                <span class="font-medium">{{ $historyItem->status->name }}</span>
                                                <span class="text-gray-500 ml-1">- {{ $historyItem->created_at->format('M d, Y H:i') }}</span>
                                            </span>
                                        </div>
                                        @if($historyItem->notes)
                                        <div class="ml-4 flex-shrink-0">
                                            <p class="text-gray-600">{{ $historyItem->notes }}</p>
                                        </div>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">&copy; {{ date('Y') }} {{ $workOrder->organization->name }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
