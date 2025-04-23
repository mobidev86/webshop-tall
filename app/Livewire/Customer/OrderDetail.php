<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    public Order $order;

    public $showCancelConfirmation = false;

    public function mount($orderId)
    {
        // Find the order and verify it belongs to the current user
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $order) {
            session()->flash('error', 'Order not found or you do not have permission to view it.');

            return redirect()->route('customer.orders');
        }

        $this->order = $order;
    }

    public function confirmCancel()
    {
        $this->showCancelConfirmation = true;
    }

    public function cancelOrder()
    {
        // Check if the order can be cancelled
        if (! $this->order->canBeCancelled()) {
            session()->flash('error', 'This order cannot be cancelled.');
            $this->showCancelConfirmation = false;

            return;
        }

        // Attempt to cancel the order
        if ($this->order->cancel()) {
            session()->flash('success', 'Order has been cancelled successfully.');
        } else {
            session()->flash('error', 'Failed to cancel the order. Please try again.');
        }

        $this->showCancelConfirmation = false;
    }

    public function closeModal()
    {
        $this->showCancelConfirmation = false;
    }

    public function render()
    {
        return view('livewire.customer.order-detail', [
            'order' => $this->order,
            'items' => $this->order->items()->with('product')->get(),
        ]);
    }
}
