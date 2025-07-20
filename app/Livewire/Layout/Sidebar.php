<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Sidebar extends Component
{

    public $page = 'dashboard';

    public function setPage($page)
    {
        $this->page = $page;

        //$this->dispatch('page-changed', page: $page)->to('system');
        $this->dispatch('page-changed', page: $page);
    }

    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}
