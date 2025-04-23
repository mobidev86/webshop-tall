<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    // Listen for the recalculate-total event
    protected $listeners = ['recalculate-total' => 'recalculateTotal'];

    // Method to recalculate the total amount
    public function recalculateTotal()
    {
        $data = $this->data;
        $items = $data['items'] ?? [];

        $total = 0;

        foreach ($items as $item) {
            $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                ? (int) $item['quantity']
                : 0;

            $price = isset($item['price']) && is_numeric($item['price'])
                ? (float) $item['price']
                : 0;

            $total += $price * $quantity;
        }

        $this->data['total_amount'] = number_format($total, 2, '.', '');
    }

    // Handle saving the order items relationship after the order is created
    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::beginTransaction();

            // Extract order items and calculate total
            $orderItems = $data['items'] ?? [];
            unset($data['items']);

            // Pre-calculate total from items
            $calculatedTotal = 0;
            foreach ($orderItems as $itemData) {
                if (! isset($itemData['product_id'])) {
                    continue;
                }

                $product = Product::find($itemData['product_id']);
                if (! $product) {
                    continue;
                }

                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
                $price = (float) $product->getCurrentPrice();
                $calculatedTotal += $price * $quantity;
            }

            // Set calculated total
            $data['total_amount'] = $calculatedTotal > 0 ? $calculatedTotal : (float) ($data['total_amount'] ?? 0.00);

            // Generate order number if not provided
            if (empty($data['order_number'])) {
                $data['order_number'] = Order::generateOrderNumber();
            }

            // Create order record
            $order = static::getModel()::create($data);
            $orderId = $order->id;

            // Process order items
            foreach ($orderItems as $itemData) {
                if (! isset($itemData['product_id'])) {
                    continue;
                }

                // Get product with locking to prevent race conditions
                $product = Product::lockForUpdate()->find($itemData['product_id']);
                if (! $product) {
                    continue;
                }

                // Determine quantity (respecting stock limits)
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
                if ($product->stock < $quantity) {
                    $quantity = max(0, $product->stock);
                    if ($quantity <= 0) {
                        continue;
                    }
                }

                // Use product's current price
                $price = (float) $product->getCurrentPrice();
                $subtotal = $price * $quantity;

                // Create order item
                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product->stock -= $quantity;
                $product->save();
            }

            // Get actual total from saved items
            $finalTotal = OrderItem::where('order_id', $orderId)->sum('subtotal');

            // Update order total if items were created successfully
            if ($finalTotal > 0) {
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update(['total_amount' => $finalTotal]);

                // Update model instance
                $order->total_amount = $finalTotal;
            }

            DB::commit();

            // Return fresh model
            return Order::find($orderId);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
