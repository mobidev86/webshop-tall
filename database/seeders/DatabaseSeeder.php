<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        // Update role if the admin user already existed
        if (! $admin->wasRecentlyCreated && $admin->role !== User::ROLE_ADMIN) {
            $admin->update(['role' => User::ROLE_ADMIN]);
        }

        // Create sample customer users
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CUSTOMER,
                'phone' => '123-456-7890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip_code' => '10001',
                'country' => 'USA',
                'is_active' => true,
                'notes' => 'Regular customer',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CUSTOMER,
                'phone' => '098-765-4321',
                'address' => '456 Park Ave',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip_code' => '90001',
                'country' => 'USA',
                'is_active' => true,
                'notes' => 'Premium customer',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CUSTOMER,
                'phone' => '555-123-4567',
                'address' => '789 Elm St',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip_code' => '60007',
                'country' => 'USA',
                'is_active' => true,
                'notes' => 'New customer',
            ],
        ];

        foreach ($customers as $customerData) {
            User::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }

        // Create categories
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashion and apparel',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Kitchen',
                'slug' => 'home-kitchen',
                'description' => 'Home and kitchen products',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create sub-categories
        $subCategories = [
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'description' => 'Mobile phones and accessories',
                'parent_id' => Category::where('slug', 'electronics')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'description' => 'Notebooks and laptops',
                'parent_id' => Category::where('slug', 'electronics')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'description' => 'Clothing for men',
                'parent_id' => Category::where('slug', 'clothing')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Women\'s Clothing',
                'slug' => 'womens-clothing',
                'description' => 'Clothing for women',
                'parent_id' => Category::where('slug', 'clothing')->first()->id,
                'is_active' => true,
            ],
        ];

        foreach ($subCategories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create products and associate with categories
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'The latest iPhone with advanced features',
                'features' => 'A17 Pro chip, 48MP camera, 6.1-inch display',
                'price' => 999.99,
                'stock' => 50,
                'sku' => 'IPHONE15PRO',
                'is_active' => true,
                'is_featured' => true,
                'categories' => ['smartphones'],
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'description' => 'Flagship Samsung smartphone',
                'features' => 'Snapdragon 8 Gen 3, 50MP camera, 6.2-inch display',
                'price' => 899.99,
                'stock' => 30,
                'sku' => 'SAMSUNGS24',
                'is_active' => true,
                'is_featured' => true,
                'categories' => ['smartphones'],
            ],
            [
                'name' => 'MacBook Pro 16"',
                'slug' => 'macbook-pro-16',
                'description' => 'Professional laptop by Apple',
                'features' => 'M3 Pro chip, 16GB RAM, 512GB SSD',
                'price' => 2499.99,
                'stock' => 15,
                'sku' => 'MBPRO16M3',
                'is_active' => true,
                'is_featured' => false,
                'categories' => ['laptops', 'electronics'],
            ],
            [
                'name' => 'Men\'s Casual T-Shirt',
                'slug' => 'mens-casual-tshirt',
                'description' => 'Comfortable cotton t-shirt for men',
                'features' => '100% cotton, Machine washable, Various colors',
                'price' => 24.99,
                'sale_price' => 19.99,
                'stock' => 100,
                'sku' => 'MENTSHIRT1',
                'is_active' => true,
                'is_featured' => false,
                'categories' => ['mens-clothing', 'clothing'],
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'slug' => 'womens-summer-dress',
                'description' => 'Light and comfortable summer dress',
                'features' => 'Floral pattern, Light fabric, Knee length',
                'price' => 49.99,
                'sale_price' => 39.99,
                'stock' => 75,
                'sku' => 'WOMDRESS1',
                'is_active' => true,
                'is_featured' => true,
                'categories' => ['womens-clothing', 'clothing'],
            ],
        ];

        foreach ($products as $productData) {
            $categories = $productData['categories'];
            unset($productData['categories']);

            $product = Product::firstOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );

            // Associate categories with the product
            $categoryIds = [];
            foreach ($categories as $categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category) {
                    $categoryIds[] = $category->id;
                }
            }

            $product->categories()->sync($categoryIds);
        }
    }
}
