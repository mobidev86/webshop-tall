<?php

namespace App\Livewire\Shop;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class SearchBar extends Component
{
    /**
     * The search query string
     */
    public string $search = '';

    /**
     * Whether the search bar is active/expanded
     */
    public bool $isActive = false;

    /**
     * Initialize the component
     */
    public function mount(): void
    {
        // Check if we have a query parameter for search
        if (request()->has('search')) {
            $this->search = (string) request()->query('search', '');
        }
    }

    /**
     * Toggle the search bar visibility
     */
    public function toggleSearch(): void
    {
        $this->isActive = ! $this->isActive;
    }

    /**
     * Clear the search input
     */
    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * Submit the search query
     *
     * @return mixed Return value is only used if redirecting
     */
    public function submitSearch()
    {
        // If search is empty, dispatch with empty string to show all products
        if (Route::currentRouteName() === 'shop.index') {
            $this->dispatch('search-submitted', search: $this->search);
            $this->isActive = false;

            return null;
        } else {
            // If we're on a different page, redirect to the shop page with the search query
            return redirect()->route('shop.index', ['search' => $this->search]);
        }
    }

    /**
     * Render the component
     */
    public function render(): View
    {
        return view('livewire.shop.search-bar');
    }
}
