<?php

namespace App\Livewire\Customer;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProfileManagement extends Component
{
    public function render()
    {
        return view('livewire.customer.profile-management');
    }
}
