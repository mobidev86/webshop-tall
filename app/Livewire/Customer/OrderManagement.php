<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderManagement extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status = '';
    public $sort = 'created_at';
    public $sortDirection = 'desc';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatus()
    {
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
    
    public function render()
    {
        return view('livewire.customer.order-management', [
            'orders' => $this->orders,
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
