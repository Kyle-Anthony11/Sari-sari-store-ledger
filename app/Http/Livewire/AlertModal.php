<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class AlertModal extends Component
{
    public string $message = '';
    public bool $show = false;

    #[On('showAlert')]
    public function display(string $message): void
    {
        $this->message = $message;
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.alert-modal');
    }
}
