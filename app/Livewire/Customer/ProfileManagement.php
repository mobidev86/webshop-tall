<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ProfileManagement extends Component
{
    public function render()
    {
        return view('livewire.customer.profile-management');
    }
}
