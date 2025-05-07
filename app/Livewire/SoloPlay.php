<?php

namespace App\Livewire;

use Livewire\Component;

class SoloPlay extends Component
{

    public $tickets = 10; // Static tickets for testing
    public $currentFloor = 1;
    public $entriesThisRun = 0;
    public $gameOver = false;
    public $message = '';
    public $correctTicket;

    public function mount()
    {
        $this->newRound();
    }

    public function newRound()
    {
        $this->gameOver = false;
        $this->entriesThisRun = 0;
        $this->currentFloor = 1;
        $this->correctTicket = rand(0, 1);
        $this->message = '';
    }

    public function pickTicket($choice)
    {
        if ($this->gameOver) return;

        if ($this->tickets <= 0) {
            $this->message = 'You need at least 1 ticket to play!';
            return;
        }

        if ($choice == $this->correctTicket) {
            $this->entriesThisRun++;
            $this->currentFloor++;
            $this->correctTicket = rand(0, 1);
            $this->message = 'Correct! Moving to floor ' . $this->currentFloor . '.';
        } else {
            $this->gameOver = true;
            $this->message = 'Wrong choice! You fell off the tower.';
        }
    }
    public function render()
    {
        return view('livewire.solo-play');
    }
}
