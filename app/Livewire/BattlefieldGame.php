<?php

namespace App\Livewire;

use Livewire\Component;

class BattlefieldGame extends Component
{
    public $tickets = 100;
    public $userTiles = [];
    public $botTiles = [];
    public $userCorrectTiles = [];
    public $botCorrectTiles = [];
    public $userRevealed = [];
    public $botRevealed = [];
    public $userCurrentRow = 4;
    public $botCurrentRow = 4;
    public $userScore = 0;
    public $botScore = 0;
    public $activePlayer = 'user';
    public $roundsPlayed = 0;
    public $gameActive = false;
    public $winner = null;

    public function mount()
    {
        $this->startNewGame();
    }

    public function startNewGame()
    {
        if ($this->tickets < 10) {
            session()->flash('error', 'Not enough tickets to play.');
            return;
        }

        $this->tickets -= 10;
        $this->gameActive = true;
        $this->roundsPlayed = 0;
        $this->userScore = 0;
        $this->botScore = 0;
        $this->winner = null;

        $this->resetPlayerTower('user');
        $this->resetPlayerTower('bot');
        $this->activePlayer = 'user';
        $this->dispatch('play-sound', sound: 'play');
    }

    public function revealTile($index)
    {
        if (!$this->gameActive || $this->activePlayer !== 'user') return;

        // Only allow tiles from the current row to be revealed
        $rowStart = $this->userCurrentRow * 2;
        $rowEnd = $rowStart + 1;

        // Ensure only current row tiles can be revealed
        if ($index !== $rowStart && $index !== $rowEnd) return;

        $this->processTurn($index, 'user');
    }

    public function botPlay()
    {
        if (!$this->gameActive || $this->activePlayer !== 'bot') return;

        while ($this->botCurrentRow >= 0) {
            $rowStart = $this->botCurrentRow * 2;
            $rowEnd = $rowStart + 1;
            $correctIndex = $this->botCorrectTiles[4 - $this->botCurrentRow];

            // Bot selects the correct tile with 60% chance
            $picked = rand(1, 100) <= 60 ? $correctIndex : ($correctIndex == $rowStart ? $rowEnd : $rowStart);
            $this->processTurn($picked, 'bot');

            // Break if the bot hits a wrong tile
            if ($picked !== $correctIndex) break;
        }

        // End the round if bot makes a mistake
        if ($this->roundsPlayed < 20) {
            $this->roundsPlayed++;
            $this->resetPlayerTower('user');
            $this->activePlayer = 'user';
        }

        if ($this->roundsPlayed >= 20) {
            $this->endGame();
        }
    }

    public function processTurn($index, $player)
    {
        $row = $player === 'user' ? $this->userCurrentRow : $this->botCurrentRow;
        $rowStart = $row * 2;
        $rowEnd = $rowStart + 1;
        $correctIndex = ($player === 'user' ? $this->userCorrectTiles : $this->botCorrectTiles)[4 - $row];

        if ($index === $correctIndex) {
            $this->{$player . 'Tiles'}[$rowStart] = 'ticket';
            $this->{$player . 'Tiles'}[$rowEnd] = 'ticket';
            $this->{$player . 'Revealed'}[] = $rowStart;
            $this->{$player . 'Revealed'}[] = $rowEnd;

            $this->{$player . 'CurrentRow'}--;
            $this->dispatch('play-sound', sound: 'correct');

            if ($this->{$player . 'CurrentRow'} < 0) {
                $this->{$player . 'Score'}++;
                $this->roundsPlayed++;
                $this->resetPlayerTower($player);
                $this->resetPlayerTower($player === 'user' ? 'bot' : 'user');
                $this->activePlayer = $player === 'user' ? 'bot' : 'user';
            }
        } else {
            $this->{$player . 'Tiles'}[$index] = 'empty';
            $this->{$player . 'Revealed'}[] = $index;
            $this->dispatch('play-sound', sound: 'wrong');

            if ($player === 'user') {
                $this->activePlayer = 'bot';
                $this->botPlay();
            }
        }
    }

    private function resetPlayerTower($player)
    {
        $this->{$player . 'Tiles'} = array_fill(0, 10, 'hidden');
        $this->{$player . 'Revealed'} = [];
        $this->{$player . 'CurrentRow'} = 4;
        $this->{$player . 'CorrectTiles'} = [];

        for ($i = 4; $i >= 0; $i--) {
            $this->{$player . 'CorrectTiles'}[] = rand($i * 2, $i * 2 + 1);
        }
    }

    private function endGame()
    {
        $this->gameActive = false;
        $this->winner = $this->userScore > $this->botScore ? 'user' : ($this->botScore > $this->userScore ? 'bot' : 'draw');
        $msg = $this->winner === 'draw' ? "It's a draw!" : strtoupper($this->winner) . ' wins by score!';
        session()->flash('info', $msg);
    }

    public function render()
    {
        return view('livewire.battlefield-game');
    }
}
