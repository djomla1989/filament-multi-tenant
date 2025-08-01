<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $workOrder->order_number }} - Work Order Tracking</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('detailsToggle', () => ({
                open: false,
                toggle() {
                    this.open = !this.open;
                }
            }));

            Alpine.data('notificationPreferences', () => ({
                updatePreference(channel, enabled) {
                    fetch(`/track/{{ $workOrder->tracking_token }}/notifications`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ channel, enabled })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            this.$dispatch('notification', {
                                message: 'Notification preferences updated successfully',
                                type: 'success'
                            });

                            // Update toggle visually
                            const button = document.querySelector(`[data-channel="${channel}"]`);
                            if (button) {
                                button.setAttribute('aria-checked', enabled);
                                if (enabled) {
                                    button.classList.add('bg-indigo-600');
                                    button.classList.remove('bg-gray-200');
                                    button.querySelector('span[aria-hidden="true"]').classList.add('translate-x-5');
                                    button.querySelector('span[aria-hidden="true"]').classList.remove('translate-x-0');
                                } else {
                                    button.classList.add('bg-gray-200');
                                    button.classList.remove('bg-indigo-600');
                                    button.querySelector('span[aria-hidden="true"]').classList.add('translate-x-0');
                                    button.querySelector('span[aria-hidden="true"]').classList.remove('translate-x-5');
                                }
                            }
                        } else {
                            // Show error message
                            this.$dispatch('notification', {
                                message: data.message || 'Failed to update notification preferences',
                                type: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.$dispatch('notification', {
                            message: 'An error occurred while updating preferences',
                            type: 'error'
                        });
                    });
                }
            }));
        });
    </script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900">Work Order Tracking</h1>
                    <div class="text-sm text-gray-600 text-right">
                        <p><strong>{{ $workOrder->organization->name}}</strong></p>
                        <p>CNPJ/CPF: {{ $workOrder->organization->document_number}}</p>
                        <p>{{ $workOrder->organization->address }}, {{ $workOrder->organization->address_number }}</p>
                        <p>{{ $workOrder->organization->zip_code}} - {{ $workOrder->organization->country }}</p>
                        <p>Phone: {{ $workOrder->organization->phone }}</p>
                        @if($workOrder->organization->email)
                        <p>Email: {{ $workOrder->organization->email}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Work Order Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">{{ $workOrder->title }}</h2>
                                <p class="text-gray-600 mt-1">Order #: {{ $workOrder->order_number }}</p>
                            </div>
                            <div>
                                <span
                                    class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    {{ $workOrder->currentStatus->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Work Order Details -->
                    <div class="md:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Work Order Details</h3>

                                <div class="space-y-4">
                                    @if($workOrder->description)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                        <p class="mt-1">{{ $workOrder->description }}</p>
                                    </div>
                                    @endif

                                    @if($workOrder->notes)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Initial Notes</h4>
                                        <p class="mt-1">{{ $workOrder->notes }}</p>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Category</h4>
                                            <p class="mt-1">{{ $workOrder->workCategory->name }}</p>

                                            @if($workOrder->attributeValues && $workOrder->attributeValues->count() > 0)
                                            <div x-data="{ open: false }" class="mt-2">
                                                <button
                                                    @click="open = !open"
                                                    type="button"
                                                    class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center"
                                                >
                                                    <span x-text="open ? 'Hide attributes' : 'Show attributes'">Show attributes</span>
                                                    <svg x-show="!open" class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                    <svg x-show="open" class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>

                                                <div x-show="open" class="mt-2 pl-2 border-l-2 border-indigo-200" style="display: none;">
                                                    @foreach($workOrder->attributeValues as $attributeValue)
                                                    <div class="mb-2">
                                                        <h5 class="text-xs font-medium text-gray-500">{{ $attributeValue->attribute->name }}</h5>
                                                        <p class="text-sm">{{ $attributeValue->value }}</p>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        @if($workOrder->estimated_completion_date)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Estimated Completion</h4>
                                            <p class="mt-1">{{ $workOrder->estimated_completion_date->format('M d, Y') }}</p>
                                        </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Work Order History -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Work Order History</h3>

                                @if($historyItems->count() > 0)
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        @foreach($historyItems as $index => $item)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white bg-green-500">
                                                            <!-- Heroicon name: mini/check -->
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Status changed to <span class="px-1 py-1 font-semibold rounded-full bg-indigo-100 text-indigo-800">{{ $item->status->name }}</span></p>
                                                            <p class="mt-1 text-sm text-gray-700">{{ $item->notes }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time datetime="{{ $item->created_at->format('Y-m-d h:m') }}">{{ $item->created_at->format('d/m/Y H:i') }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @else
                                <p class="text-gray-500">No history available.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="md:col-span-1 space-y-6">
                        <!-- Customer Information -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h3>

                                <div class="space-y-3">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                        <p class="mt-1">{{ \App\Helpers\MaskHelper::maskString($workOrder->customer->name) }}</p>
                                    </div>

                                    @if($workOrder->customer->email)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Email</h4>
                                        <p class="mt-1">{{ \App\Helpers\MaskHelper::maskEmail($workOrder->customer->email) }}</p>
                                    </div>
                                    @endif

                                    @if($workOrder->customer->phone)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Phone</h4>
                                        <p class="mt-1">{{ \App\Helpers\MaskHelper::maskString($workOrder->customer->phone, 4) }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Notification Preferences -->
                        @if(count($availableChannels) > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="notificationPreferences">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Notification Preferences</h3>

                                <div class="space-y-4">
                                    @foreach($availableChannels as $channel => $label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                        <button
                                            type="button"
                                            data-channel="{{ $channel }}"
                                            class="{{ $workOrder->isNotificationEnabled($channel) ? 'bg-indigo-600' : 'bg-gray-200' }} relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            role="switch"
                                            aria-checked="{{ $workOrder->isNotificationEnabled($channel) ? 'true' : 'false' }}"
                                            @click="updatePreference('{{ $channel }}', {{ $workOrder->isNotificationEnabled($channel) ? 'false' : 'true' }})"
                                        >
                                            <span class="sr-only">Toggle {{ $label }} notifications</span>
                                            <span aria-hidden="true" class="{{ $workOrder->isNotificationEnabled($channel) ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>

        <!-- Notification component -->
        <div
            x-data="{ show: false, message: '', type: 'success' }"
            x-on:notification.window="
                show = true;
                message = $event.detail.message;
                type = $event.detail.type;
                setTimeout(() => { show = false }, 3000);
            "
            x-show="show"
            x-cloak
            class="fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white"
            :class="{
                'bg-green-500': type === 'success',
                'bg-red-500': type === 'error'
            }"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
        >
            <span x-text="message"></span>
        </div>
    </div>
</body>
</html>
