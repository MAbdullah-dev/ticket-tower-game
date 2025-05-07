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
        $this->correctTiles = [];
        for ($i = 4; $i >= 0; $i--) {
            $this->correctTiles[] = rand($i * 2, $i * 2 + 1);
        }

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

        // Check if the tile is in the current row
        $rowStart = $this->currentRow * 2;
        $rowEnd = $rowStart + 1;
        if ($index < $rowStart || $index > $rowEnd) {
            session()->flash('error', 'Choose a tile from the current row.');
            return;
        }

        // Deduct a ticket only at the start of the game
        if ($this->currentRow == 4) {
            if ($this->tickets <= 0) {
                session()->flash('error', 'No tickets left. Reload to play again.');
                return;
            }
            $this->tickets--;
        }

        // Check if the selected tile is correct
        if ($index === $this->correctTiles[4 - $this->currentRow]) {
            // Correct choice, reveal the entire row
            $this->tiles[$rowStart] = 'ticket';
            $this->tiles[$rowEnd] = 'ticket';
            $this->revealedTiles[] = $rowStart;
            $this->revealedTiles[] = $rowEnd;

            // Move up to the next row
            $this->currentRow--;

            $this->dispatch('play-sound', sound: 'correct');
            // Check if the player has completed the tower
            if ($this->currentRow < 0) {
                session()->flash('success', '🎉 You built the tower! Play again to win more!');
                $this->gameActive = false;
            }
        } else {
            // Wrong choice, reset the game
            $this->tiles[$index] = 'empty';
            $this->gameActive = false;
            session()->flash('error', '💥 Wrong tile! Game over. Try again.');
            $this->dispatch('play-sound', sound: 'wrong');
        }

        // Update the view data after each reveal
        $this->updateViewData();
    }

    public function updateViewData()
    {
        // Set the data to be passed to the view
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
