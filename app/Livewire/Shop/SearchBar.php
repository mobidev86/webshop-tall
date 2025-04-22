<?php

namespace App\Livewire\Shop;

use Livewire\Component;
use Illuminate\Support\Facades\Route;

class SearchBar extends Component
{
    public $search = '';
    public $isActive = false;
    
    public function mount()
    {
        // Check if we have a query parameter for search
        if (request()->has('search')) {
            $this->search = request()->query('search');
        }
    }
    
    public function toggleSearch()
    {
        $this->isActive = !$this->isActive;
    }
    
    public function clearSearch()
    {
        $this->search = '';
    }
    
    public function submitSearch()
    {
        // If search is empty, dispatch with empty string to show all products
        if (Route::currentRouteName() === 'shop.index') {
            $this->dispatch('search-submitted', search: $this->search);
            $this->isActive = false;
        } else {
            // If we're on a different page, redirect to the shop page with the search query
            return redirect()->route('shop.index', ['search' => $this->search]);
        }
    }
    
    public function render()
    {
        return view('livewire.shop.search-bar');
    }
} 