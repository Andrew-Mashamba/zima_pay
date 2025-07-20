<?php

namespace App\Livewire;

use Livewire\Component;

class System extends Component
{
    public $page = 'dashboard';



    #[On('page-changed')]
    public function setPage($page)
    {
        //dd($page);
        $this->page = $page;
    }


    public function render()
    {
        return view('livewire.system')
            ->layout('layouts.app');
    }
}
