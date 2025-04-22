<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    
    // Method to recalculate the order total during form interactions
    public function calculateTotalAmount(): void
    {
        $data = $this->data;
        $items = $data['items'] ?? [];
        
        $total = 0;
        foreach ($items as $item) {
            if (isset($item['subtotal'])) {
                $total += floatval($item['subtotal']);
            } elseif (isset($item['price']) && isset($item['quantity'])) {
                $total += floatval($item['price']) * intval($item['quantity']);
            }
        }
        
        $this->data['total_amount'] = number_format($total, 2, '.', '');
    }
    
    // Handle saving the order items relationship after the order is created
    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::beginTransaction();
            
            // Remove items data from the main order data before creation
            $orderItems = $data['items'] ?? [];
            unset($data['items']);
            
            // Set initial total_amount
            $data['total_amount'] = 0;
            
            // Create the order record
            $order = static::getModel()::create($data);
            
            // Track total amount
            $totalAmount = 0;
            
            // Create order items if they exist
            foreach ($orderItems as $itemData) {
                // Skip if product_id is missing
                if (!isset($itemData['product_id'])) {
                    continue;
                }
                
                // Get product with locking to prevent race conditions
                $product = Product::lockForUpdate()->find($itemData['product_id']);
                if (!$product) {
                    continue;
                }
                
                // Get quantity
                $quantity = max(1, (int)($itemData['quantity'] ?? 1));
                
                // Check if enough stock is available
                if ($product->stock < $quantity) {
                    // Adjust quantity to available stock
                    $quantity = $product->stock;
                    if ($quantity <= 0) {
                        continue; // Skip if no stock available
                    }
                }
                
                // Always use the product's current price rather than the form input
                $price = $product->getCurrentPrice();
                $subtotal = $price * $quantity;
                
                // Create the order item
                $orderItem = new OrderItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);
                
                $order->items()->save($orderItem);
                
                // Update product stock
                $product->stock -= $quantity;
                $product->save();
                
                // Add to total
                $totalAmount += $subtotal;
            }
            
            // Update the order with the calculated total
            if ($totalAmount > 0) {
                $order->total_amount = $totalAmount;
                $order->save();
            }
            
            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            // Re-throw the exception to show the error in Filament UI
            throw $e;
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
