<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">My Orders</h2>
                
                <!-- Order Summary Card -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-gray-500">Total Orders</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $totalOrdersCount }}</span>
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-gray-500">Pending</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $statusCounts['pending'] ?? 0 }}</span>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-blue-500">Processing</span>
                        <span class="text-2xl font-bold text-blue-800">{{ $statusCounts['processing'] ?? 0 }}</span>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-green-500">Completed</span>
                        <span class="text-2xl font-bold text-green-800">{{ $statusCounts['completed'] ?? 0 }}</span>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-yellow-600">Declined</span>
                        <span class="text-2xl font-bold text-yellow-800">{{ $statusCounts['declined'] ?? 0 }}</span>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg shadow-sm p-4 flex flex-col">
                        <span class="text-sm font-medium text-red-500">Cancelled</span>
                        <span class="text-2xl font-bold text-red-800">{{ $statusCounts['cancelled'] ?? 0 }}</span>
                    </div>
                </div>
                
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search -->
                    <div class="relative md:w-64">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="search" 
                            class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Search orders..."
                        >
                    </div>
                    
                    <!-- Status Filter Dropdown with AJAX loading indicator -->
                    <div class="w-full md:w-auto" style="position: relative;">
                        <div wire:loading class="absolute inset-0 bg-white bg-opacity-60 flex items-center justify-center z-10">
                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="relative inline-block text-left w-full">
                            <button type="button" 
                                class="inline-flex w-full justify-between items-center gap-x-1.5 rounded-md bg-white px-3 py-2.5 text-sm font-medium text-gray-900 border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                id="status-filter-button"
                                aria-expanded="true"
                                aria-haspopup="true"
                                onclick="toggleStatusDropdown()">
                                <span class="flex items-center">
                                    <span class="h-2.5 w-2.5 rounded-full mr-2
                                        @if($status === '') bg-indigo-500
                                        @elseif($status === 'pending') bg-gray-500
                                        @elseif($status === 'processing') bg-blue-500
                                        @elseif($status === 'completed') bg-green-500
                                        @elseif($status === 'declined') bg-yellow-500
                                        @elseif($status === 'cancelled') bg-red-500
                                        @endif
                                    "></span>
                                    @if($status === '')
                                        All Statuses ({{ $totalOrdersCount }})
                                    @else
                                        {{ ucfirst($status) }} ({{ $statusCounts[$status] ?? 0 }})
                                    @endif
                                </span>
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="absolute z-50 mt-2 w-auto origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden"
                            id="status-filter-dropdown" style="min-width: 200px; right: 0;">
                            <div class="py-1">
                                <a wire:click="setStatus('')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === '' ? 'bg-gray-100 text-indigo-600 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-indigo-500 mr-2"></span>
                                        All Statuses
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $totalOrdersCount }}</span>
                                </a>
                                <a wire:click="setStatus('pending')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === 'pending' ? 'bg-gray-100 text-gray-800 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-gray-500 mr-2"></span>
                                        Pending
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $statusCounts['pending'] ?? 0 }}</span>
                                </a>
                                <a wire:click="setStatus('processing')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === 'processing' ? 'bg-gray-100 text-blue-600 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-blue-500 mr-2"></span>
                                        Processing
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $statusCounts['processing'] ?? 0 }}</span>
                                </a>
                                <a wire:click="setStatus('completed')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === 'completed' ? 'bg-gray-100 text-green-600 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-green-500 mr-2"></span>
                                        Completed
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $statusCounts['completed'] ?? 0 }}</span>
                                </a>
                                <a wire:click="setStatus('declined')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === 'declined' ? 'bg-gray-100 text-yellow-600 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-yellow-500 mr-2"></span>
                                        Declined
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $statusCounts['declined'] ?? 0 }}</span>
                                </a>
                                <a wire:click="setStatus('cancelled')" 
                                    href="javascript:void(0)" 
                                    class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 {{ $status === 'cancelled' ? 'bg-gray-100 text-red-600 font-medium' : 'text-gray-700' }}"
                                    onclick="document.getElementById('status-filter-dropdown').classList.add('hidden')">
                                    <span class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-red-500 mr-2"></span>
                                        Cancelled
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $statusCounts['cancelled'] ?? 0 }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Status Badge -->
                <div class="mb-4 flex items-center">
                    <span class="text-sm text-gray-600 mr-2">Current Filter:</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                        @if($status === '') bg-indigo-100 text-indigo-800
                        @elseif($status === 'pending') bg-gray-100 text-gray-800
                        @elseif($status === 'processing') bg-blue-100 text-blue-800
                        @elseif($status === 'completed') bg-green-100 text-green-800
                        @elseif($status === 'declined') bg-yellow-100 text-yellow-800
                        @elseif($status === 'cancelled') bg-red-100 text-red-800
                        @endif
                    ">
                        <span class="h-1.5 w-1.5 rounded-full mr-1
                            @if($status === '') bg-indigo-500
                            @elseif($status === 'pending') bg-gray-500
                            @elseif($status === 'processing') bg-blue-500
                            @elseif($status === 'completed') bg-green-500
                            @elseif($status === 'declined') bg-yellow-500
                            @elseif($status === 'cancelled') bg-red-500
                            @endif
                        "></span>
                        {{ $status === '' ? 'All Statuses' : ucfirst($status) }}
                    </span>
                    @if($status !== '')
                        <button 
                            wire:click="setStatus('')" 
                            type="button" 
                            class="ml-2 text-xs text-gray-500 hover:text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Orders Table -->
                <div class="overflow-x-auto" wire:loading.class="opacity-50">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('order_number')">
                                    <div class="flex items-center">
                                        Order Number
                                        @if($sort === 'order_number')
                                            <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                    <div class="flex items-center">
                                        Date
                                        @if($sort === 'created_at')
                                            <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                                    <div class="flex items-center">
                                        Total
                                        @if($sort === 'total_amount')
                                            <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Details
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($order->status === 'pending') bg-gray-100 text-gray-800
                                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                            @elseif($order->status === 'completed') bg-green-100 text-green-800
                                            @elseif($order->status === 'declined') bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @endif
                                        ">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->itemsCount() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('customer.order.detail', $order->id) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 border border-transparent rounded-md font-medium text-xs text-indigo-800 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View Details
                                            </a>
                                            
                                            @if($order->canBeCancelled())
                                                <button 
                                                    wire:click="confirmCancel({{ $order->id }})" 
                                                    type="button" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 border border-transparent rounded-md font-medium text-xs text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Cancel
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No orders found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
@if($showCancelConfirmation)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
        <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Order</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to cancel this order? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button 
                    wire:click="cancelOrder" 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Cancel Order
                </button>
                <button 
                    wire:click="closeModal" 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Go Back
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    function toggleStatusDropdown() {
        const dropdown = document.getElementById('status-filter-dropdown');
        const button = document.getElementById('status-filter-button');
        const isHidden = dropdown.classList.contains('hidden');
        
        if (isHidden) {
            // Close any other dropdowns that might be open
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown) menu.classList.add('hidden');
            });
            
            // Show this dropdown
            dropdown.classList.remove('hidden');
            
            // Position the dropdown right below the button
            const buttonRect = button.getBoundingClientRect();
            const dropdownWidth = Math.max(buttonRect.width, 200);
            
            // Set dropdown position
            dropdown.style.position = 'fixed';
            dropdown.style.width = `${dropdownWidth}px`;
            dropdown.style.left = `${buttonRect.left}px`;
            dropdown.style.top = `${buttonRect.bottom + window.scrollY + 5}px`;
            dropdown.style.zIndex = '100'; // Ensure high z-index
            
            // Ensure dropdown is fully visible within the viewport
            const viewportHeight = window.innerHeight;
            const dropdownRect = dropdown.getBoundingClientRect();
            
            if (dropdownRect.bottom > viewportHeight) {
                // If dropdown would go off bottom of screen, position it above the button instead
                dropdown.style.top = `${buttonRect.top + window.scrollY - dropdownRect.height - 5}px`;
            }
        } else {
            dropdown.classList.add('hidden');
        }
    }
    
    // Update dropdown position when window is resized
    window.addEventListener('resize', function() {
        const dropdown = document.getElementById('status-filter-dropdown');
        if (!dropdown.classList.contains('hidden')) {
            toggleStatusDropdown();
            toggleStatusDropdown();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('status-filter-dropdown');
        const button = document.getElementById('status-filter-button');
        
        if (!dropdown.classList.contains('hidden') && 
            !dropdown.contains(event.target) && 
            !button.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Add click handlers to all dropdown items
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#status-filter-dropdown a').forEach(item => {
            item.addEventListener('click', function() {
                document.getElementById('status-filter-dropdown').classList.add('hidden');
            });
        });
    });
</script>
