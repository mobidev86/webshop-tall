<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Profile Settings</h1>
            <p class="text-gray-600">Manage your account information and preferences</p>
        </div>
        
        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Account Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <livewire:profile.update-profile-information-form />
                    </div>
                    
                    <div>
                        <livewire:profile.update-password-form />
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Management</h2>
                    <p class="text-gray-600 mb-4">View and manage your orders</p>
                    
                    <a href="{{ route('customer.orders') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Orders
                    </a>
                </div>
            </div>
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Account Deletion</h2>
                            <p class="text-gray-600 mb-4">Permanently delete your account</p>
                        </div>
                    </div>
                    
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</div>
