<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load items relationship for display in the form
        $this->record->load('items');
        
        return $data;
    }
    
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
    
    // Handle saving the updated order items
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            DB::beginTransaction();
            
            // Remove items data from the main order data before update
            $orderItems = $data['items'] ?? [];
            unset($data['items']);
            
            // Check if order status is changing to cancelled
            $statusChangingToCancelled = isset($data['status']) 
                && $data['status'] === 'cancelled' 
                && $record->status !== 'cancelled';
            
            // Update the order record (basic information)
            $record->update($data);
            
            // Get existing items with products for inventory management
            $existingItems = $record->items()->with('product')->get();
            $existingItemIds = $existingItems->pluck('id')->toArray();
            $updatedItemIds = [];
            
            // Track total amount
            $totalAmount = 0;
            
            // If order is being cancelled, restore stock for all items and skip item processing
            if ($statusChangingToCancelled) {
                foreach ($existingItems as $item) {
                    if ($item->product) {
                        $item->product->stock += $item->quantity;
                        $item->product->save();
                    }
                }
            } else {
                // Process order items for regular updates
                foreach ($orderItems as $itemData) {
                    // Skip if no product_id
                    if (!isset($itemData['product_id'])) {
                        continue;
                    }
                    
                    $newQuantity = max(1, (int)($itemData['quantity'] ?? 1));
                    
                    // Check if this is an existing item or a new one
                    $isNewItem = !isset($itemData['id']);
                    $existingItem = null;
                    
                    if (!$isNewItem) {
                        $existingItem = $existingItems->firstWhere('id', $itemData['id']);
                    }
                    
                    // Different logic for existing vs new items
                    if ($existingItem) {
                        // Handle existing item
                        
                        // Check if product has changed
                        $productChanged = $existingItem->product_id != $itemData['product_id'];
                        
                        if ($productChanged) {
                            // Product changed - restore stock to original product
                            $originalProduct = $existingItem->product;
                            if ($originalProduct) {
                                $originalProduct->stock += $existingItem->quantity;
                                $originalProduct->save();
                            }
                            
                            // Get and check new product stock
                            $newProduct = Product::lockForUpdate()->find($itemData['product_id']);
                            if (!$newProduct || $newProduct->stock < $newQuantity) {
                                // Not enough stock
                                if ($newProduct) {
                                    // Adjust quantity to available stock
                                    $newQuantity = min($newQuantity, $newProduct->stock);
                                    if ($newQuantity <= 0) {
                                        continue; // Skip if no stock
                                    }
                                    
                                    // Reduce stock for new product
                                    $newProduct->stock -= $newQuantity;
                                    $newProduct->save();
                                } else {
                                    continue; // Skip if product doesn't exist
                                }
                            } else {
                                // Enough stock, reduce it
                                $newProduct->stock -= $newQuantity;
                                $newProduct->save();
                            }
                            
                            // Update item with new product info - always use the product's current price
                            $price = $newProduct->getCurrentPrice();
                            $subtotal = $price * $newQuantity;
                            
                            $existingItem->update([
                                'product_id' => $newProduct->id,
                                'product_name' => $newProduct->name,
                                'quantity' => $newQuantity,
                                'price' => $price,
                                'subtotal' => $subtotal,
                            ]);
                        } else {
                            // Same product, just quantity/price changed
                            
                            // Calculate quantity change
                            $quantityDiff = $newQuantity - $existingItem->quantity;
                            
                            // Handle stock changes if quantity changed
                            if ($quantityDiff != 0) {
                                $product = $existingItem->product;
                                
                                if ($quantityDiff > 0) {
                                    // Quantity increased - check stock
                                    if ($product->stock < $quantityDiff) {
                                        // Not enough stock, adjust quantity
                                        $newQuantity = $existingItem->quantity + $product->stock;
                                        $quantityDiff = $product->stock;
                                        
                                        if ($quantityDiff <= 0) {
                                            continue; // Skip if no additional stock
                                        }
                                    }
                                    
                                    // Decrease stock
                                    $product->stock -= $quantityDiff;
                                } else {
                                    // Quantity decreased - restore stock
                                    $product->stock += abs($quantityDiff);
                                }
                                
                                $product->save();
                            }
                            
                            // Update the item - keep using the existing price since price editing is disabled
                            $price = $existingItem->price;
                            $subtotal = $price * $newQuantity;
                            
                            $existingItem->update([
                                'quantity' => $newQuantity,
                                'subtotal' => $subtotal,
                            ]);
                        }
                        
                        $updatedItemIds[] = $existingItem->id;
                        $totalAmount += $existingItem->subtotal;
                        
                    } else {
                        // Handle new item
                        
                        // Get product and check stock
                        $product = Product::lockForUpdate()->find($itemData['product_id']);
                        if (!$product) {
                            continue;
                        }
                        
                        if ($product->stock < $newQuantity) {
                            // Not enough stock, adjust quantity
                            $newQuantity = min($newQuantity, $product->stock);
                            if ($newQuantity <= 0) {
                                continue; // Skip if no stock
                            }
                        }
                        
                        // Reduce stock
                        $product->stock -= $newQuantity;
                        $product->save();
                        
                        // Create new order item - always use the product's current price
                        $price = $product->getCurrentPrice();
                        $subtotal = $price * $newQuantity;
                        
                        $orderItem = OrderItem::create([
                            'order_id' => $record->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $newQuantity,
                            'price' => $price,
                            'subtotal' => $subtotal,
                        ]);
                        
                        $updatedItemIds[] = $orderItem->id;
                        $totalAmount += $subtotal;
                    }
                }
                
                // Handle removed items - restore stock
                $removedItemIds = array_diff($existingItemIds, $updatedItemIds);
                foreach ($existingItems as $item) {
                    if (in_array($item->id, $removedItemIds) && $item->product) {
                        // Item was removed, restore stock
                        $item->product->stock += $item->quantity;
                        $item->product->save();
                    }
                }
            }
            
            // Delete any items that weren't in the updated list
            if (!empty($updatedItemIds)) {
                OrderItem::where('order_id', $record->id)
                    ->whereNotIn('id', $updatedItemIds)
                    ->delete();
            }
            
            // Recalculate total amount
            $record->calculateTotalAmount();
            
            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
