<?php

namespace App\Livewire;

use Livewire\Component;

class SoloPlay extends Component
{
    public $tickets = 100; // Static ticket count for now
    public $tiles = [];
    public $revealedTiles = [];
    public $currentRow;
    public $gameActive = true;
    public $correctTiles = [];
    public $viewData = [];

    public function mount()
    {
        // Initialize the game on component load
        $this->initializeGame();
    }

    public function initializeGame()
    {
        // Reset the game state
        $this->tiles = array_fill(0, 10, 'hidden');
        $this->revealedTiles = [];
        $this->gameActive = true;

        // Start from the bottom row (last row, moving up)
        $this->currentRow = 4;

        // Randomly assign one correct tile in each row
        // $this->correctTiles = [];
        // for ($i = 4; $i >= 0; $i--) {
        //     $this->correctTiles[] = rand($i * 2, $i * 2 + 1);
        // }

        // Set the initial view data
        $this->updateViewData();
        $this->dispatch('play-sound', sound: 'play');
    }

    public function revealTile($index)
    {
        // Check if the game is active
        if (!$this->gameActive) {
            session()->flash('error', 'Game over! Click "Playing" to try again.');
            return;
        }

        $rowStart = $this->currentRow * 2;
        $rowEnd = $rowStart + 1;
        if ($index < $rowStart || $index > $rowEnd) {
            session()->flash('error', 'Choose a tile from the current row.');
            return;
        }

        if ($this->currentRow == 4) {
            if ($this->tickets <= 0) {
                session()->flash('error', 'No tickets left. Reload to play again.');
                return;
            }
            $this->tickets--;
        }

        $isWin = mt_rand(1, 100) <= 40;
        if ($isWin) {
            $this->tiles[$rowStart] = 'ticket';
            $this->tiles[$rowEnd] = 'ticket';
            $this->revealedTiles[] = $rowStart;
            $this->revealedTiles[] = $rowEnd;

            $this->currentRow--;

            $this->dispatch('play-sound', sound: 'correct');
            if ($this->currentRow < 0) {
                session()->flash('success', '🎉 You built the tower! Play again to win more!');
                $this->gameActive = false;
            }
        } else {
            $this->tiles[$index] = 'empty';
            $this->gameActive = false;
            session()->flash('error', '💥 Wrong tile! Game over. Try again.');
            $this->dispatch('play-sound', sound: 'wrong');
        }

        $this->updateViewData();
    }

    public function updateViewData()
    {
        $this->viewData = [
            'tiles' => $this->tiles,
            'tickets' => $this->tickets,
        ];
    }
    public function render()
    {
        return view('livewire.solo-play');
    }
}
