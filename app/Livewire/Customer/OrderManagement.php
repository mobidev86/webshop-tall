<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class OrderManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $sort = 'created_at';

    public $sortDirection = 'desc';

    public $showCancelConfirmation = false;

    public $selectedOrderId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        // Initialize any default values here
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sort === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmCancel($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->showCancelConfirmation = true;
    }

    public function cancelOrder()
    {
        $order = Order::where('id', $this->selectedOrderId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $order) {
            session()->flash('error', 'Order not found or you do not have permission to cancel it.');
            $this->showCancelConfirmation = false;

            return;
        }

        // Check if the order can be cancelled
        if (! $order->canBeCancelled()) {
            session()->flash('error', 'This order cannot be cancelled.');
            $this->showCancelConfirmation = false;

            return;
        }

        // Attempt to cancel the order
        if ($order->cancel()) {
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

    public function getOrdersProperty()
    {
        return Order::where('user_id', Auth::id())
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('order_number', 'like', "%{$this->search}%")
                        ->orWhere('total_amount', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function (Builder $query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sort, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Get count of orders by status
     */
    public function getStatusCountsProperty()
    {
        $counts = Order::where('user_id', Auth::id())
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('order_number', 'like', "%{$this->search}%")
                        ->orWhere('total_amount', 'like', "%{$this->search}%");
                });
            })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses have a count
        $result = [];
        foreach (Order::VALID_STATUSES as $status) {
            $result[$status] = $counts[$status] ?? 0;
        }

        return $result;
    }

    /**
     * Get total count of all orders
     */
    public function getTotalOrdersCountProperty()
    {
        return Order::where('user_id', Auth::id())
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('order_number', 'like', "%{$this->search}%")
                        ->orWhere('total_amount', 'like', "%{$this->search}%");
                });
            })
            ->count();
    }

    public function render()
    {
        return view('livewire.customer.order-management', [
            'orders' => $this->orders,
            'statusCounts' => $this->statusCounts,
            'totalOrdersCount' => $this->totalOrdersCount,
            'statuses' => [
                Order::STATUS_PENDING => 'Pending',
                Order::STATUS_PROCESSING => 'Processing',
                Order::STATUS_COMPLETED => 'Completed',
                Order::STATUS_DECLINED => 'Declined',
                Order::STATUS_CANCELLED => 'Cancelled',
            ],
        ]);
    }
}
