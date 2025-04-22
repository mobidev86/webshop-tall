<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Customer Dashboard</h1>
            <p class="text-gray-600">Welcome back, {{ Auth::user()->name }}</p>
        </div>
        
        <!-- Order Statistics -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Order Statistics</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="text-indigo-600 text-2xl font-bold">{{ $orderStats['total'] }}</div>
                        <div class="text-indigo-900 text-sm font-medium">Total Orders</div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-yellow-600 text-2xl font-bold">{{ $orderStats['pending'] }}</div>
                        <div class="text-yellow-900 text-sm font-medium">Pending</div>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-blue-600 text-2xl font-bold">{{ $orderStats['processing'] }}</div>
                        <div class="text-blue-900 text-sm font-medium">Processing</div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-green-600 text-2xl font-bold">{{ $orderStats['completed'] }}</div>
                        <div class="text-green-900 text-sm font-medium">Completed</div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-gray-600 text-2xl font-bold">{{ $orderStats['cancelled'] }}</div>
                        <div class="text-gray-900 text-sm font-medium">Cancelled</div>
                    </div>
                </div>
                
                <div class="mt-4 text-right">
                    <a href="{{ route('customer.orders') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        View All Orders →
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Orders</h2>
                
                @if(count($recentOrders) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $order->order_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                                @elseif($order->status === 'completed') bg-green-100 text-green-800
                                                @elseif($order->status === 'declined') bg-red-100 text-red-800
                                                @elseif($order->status === 'cancelled') bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('customer.order.detail', $order->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No orders found.</p>
                @endif
                
                <div class="mt-4 text-right">
                    <a href="{{ route('customer.orders') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        View All Orders →
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('customer.orders') }}" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <div class="mr-4 bg-indigo-100 rounded-full p-2">
                            <svg class="w-6 h-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Manage Orders</h3>
                            <p class="text-sm text-gray-500">View and manage your orders</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('customer.profile') }}" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <div class="mr-4 bg-indigo-100 rounded-full p-2">
                            <svg class="w-6 h-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Profile Settings</h3>
                            <p class="text-sm text-gray-500">Update your personal information</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
