<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class BattlefieldGame extends Component
{
    public $gameStarted = false;
    public $tickets = 100;
    public $currentRound = 1;
    public $userScore = 0;
    public $botScore = 0;
    public $userTower = [];
    public $botTower = [];
    public $userCurrentRow = 0;
    public $botCurrentRow = 0;
    public $turn = 'user';
    public $firstPlayer = 'user';
    public $playersTurnsTaken = [];
    public $progress = [];
    public $gameWinner = null;

    protected $listeners = ['botTurn' => 'handleBotTurn'];

    public function mount()
    {
        $this->tickets = 100;
        Log::debug('Game mounted with initial tickets: ' . $this->tickets);
    }

    public function startGame()
    {
        Log::debug('Attempting to start game. Tickets: ' . $this->tickets);
        if ($this->tickets < 10) {
            session()->flash('message', 'Not enough tickets!');
            Log::debug('Not enough tickets to start game.');
            return;
        }

        $this->tickets -= 10;
        $this->gameStarted = true;
        $this->currentRound = 1;
        $this->userScore = 0;
        $this->botScore = 0;
        $this->progress = [];
        $this->gameWinner = null;
        Log::debug('Game started. New ticket count: ' . $this->tickets);
        $this->initializeRound();
    }

    public function initializeRound()
    {
        $this->userTower = $this->generateTower();
        $this->botTower = $this->generateTower();
        $this->userCurrentRow = 0;
        $this->botCurrentRow = 0;
        $this->firstPlayer = rand(0, 1) ? 'user' : 'bot';
        $this->turn = $this->firstPlayer;
        $this->playersTurnsTaken = [];
        Log::debug('Round initialized. First player: ' . $this->firstPlayer);

        if ($this->turn == 'bot') {
            Log::debug('Bot turn initiated at round start.');
            $this->handleBotTurn();
        }
    }

    private function generateTower()
    {
        $tower = [];
        for ($row = 0; $row < 5; $row++) {
            $correctIndex = rand(0, 1);
            $tower[$row] = [
                0 => ['correct' => $correctIndex == 0, 'selected' => false],
                1 => ['correct' => $correctIndex == 1, 'selected' => false],
            ];
        }
        return $tower;
    }

    public function selectTicket($player, $row, $ticketIndex)
    {
        Log::debug('Selecting ticket for ' . $player . ' at row ' . $row . ', ticket ' . $ticketIndex);
        if ($this->gameWinner || $this->turn != $player || $this->{$player . 'CurrentRow'} != $row) {
            Log::debug('Selection invalid: gameWinner=' . ($this->gameWinner ? 'true' : 'false') . ', turn=' . $this->turn . ', currentRow=' . $this->{$player . 'CurrentRow'});
            return;
        }

        $tower = $player . 'Tower';
        $this->$tower[$row][$ticketIndex]['selected'] = true;
        Log::debug('Ticket selected. Tower state: ' . json_encode($this->$tower));

        if ($this->$tower[$row][$ticketIndex]['correct']) {
            $this->{$player . 'CurrentRow'}++;
            Log::debug($player . ' correct selection. New row: ' . $this->{$player . 'CurrentRow'});
            if ($this->{$player . 'CurrentRow'} == 5) {
                $this->progress[$this->currentRound] = [
                    'user' => $player == 'user' ? 5 : $this->userCurrentRow,
                    'bot' => $player == 'bot' ? 5 : $this->botCurrentRow,
                ];
                $this->userScore += $this->progress[$this->currentRound]['user'];
                $this->botScore += $this->progress[$this->currentRound]['bot'];
                $this->gameWinner = $player;
                Log::debug($player . ' wins the game!');
                $this->dispatch('botMove', ['continue' => false]);
                return;
            }
            if ($player == 'bot') {
                Log::debug('Bot made a correct selection, dispatching botMove with continue=true');
                $this->dispatch('botMove', ['continue' => true]);
            }
        } else {
            Log::debug($player . ' incorrect selection at row ' . $row);
            $this->dispatch('botMove', ['continue' => false]);
            $this->endTurn($player);
        }
    }

    private function botSelectTicket()
    {
        if ($this->gameWinner || $this->turn != 'bot') {
            Log::debug('Bot cannot select: gameWinner=' . ($this->gameWinner ? 'true' : 'false') . ', turn=' . $this->turn);
            return false;
        }

        $row = $this->botCurrentRow;
        $knowsCorrect = rand(0, 99) < 20; // 20% chance to know correct
        if ($knowsCorrect) {
            $correctIndex = array_search(true, array_column($this->botTower[$row], 'correct'));
            $ticketIndex = $correctIndex;
        } else {
            $ticketIndex = rand(0, 1);
        }
        Log::debug('Bot selecting ticket ' . $ticketIndex . ' at row ' . $row . ' (knowsCorrect: ' . ($knowsCorrect ? 'true' : 'false') . ')');

        $this->selectTicket('bot', $row, $ticketIndex);
        return $this->botCurrentRow < 5 && $this->botTower[$row][$ticketIndex]['correct'] && !$this->gameWinner;
    }

    public function handleBotTurn()
    {
        Log::debug('Bot turn triggered via botTurn event. Current row: ' . $this->botCurrentRow);
        $this->botSelectTicket();
    }

    private function endTurn($player)
    {
        Log::debug($player . ' turn ended. Players turns taken: ' . count($this->playersTurnsTaken));
        $this->playersTurnsTaken[] = $player;
        if (count($this->playersTurnsTaken) == 2) {
            Log::debug('Both players have taken turns. Ending round ' . $this->currentRound);
            $this->endRound();
        } else {
            $nextPlayer = $player == 'user' ? 'bot' : 'user';
            $this->turn = $nextPlayer;
            Log::debug('Switching turn to ' . $nextPlayer);
            if ($nextPlayer == 'bot') {
                $this->handleBotTurn();
            }
        }
    }

    private function endRound()
    {
        $this->progress[$this->currentRound] = [
            'user' => $this->userCurrentRow,
            'bot' => $this->botCurrentRow,
        ];
        $this->userScore += $this->userCurrentRow;
        $this->botScore += $this->botCurrentRow;
        Log::debug('Round ' . $this->currentRound . ' ended. Scores - User: ' . $this->userScore . ', Bot: ' . $this->botScore);

        if ($this->currentRound == 20) {
            if ($this->userScore > $this->botScore) {
                $this->gameWinner = 'user';
            } elseif ($this->botScore > $this->userScore) {
                $this->gameWinner = 'bot';
            } else {
                $this->gameWinner = 'tie';
            }
            Log::debug('Game ended after 20 rounds. Winner: ' . ($this->gameWinner ?: 'None (Tie)'));
        } else {
            $this->currentRound++;
            Log::debug('Advancing to round ' . $this->currentRound);
            $this->initializeRound();
        }
    }

    public function render()
    {
        return view('livewire.battlefield-game');
    }
}