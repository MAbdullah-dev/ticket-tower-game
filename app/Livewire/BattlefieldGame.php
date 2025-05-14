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
    public $userRowStates = [];
    public $botRowStates = [];

    protected $listeners = [
        'botTurn' => 'handleBotTurn',
        'endRound' => 'endRound',
    ];

    public function mount()
    {
        $this->tickets = 100;
        $this->userRowStates = [];
        $this->botRowStates = [];
        Log::debug('Game mounted with initial tickets: ' . $this->tickets);
    }

    public function startGame()
    {
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
        $this->firstPlayer = rand(0, 1) ? 'user' : 'bot';
        $this->turn = $this->firstPlayer;
        Log::debug('Game started. First player: ' . $this->firstPlayer . ', New ticket count: ' . $this->tickets);
        $this->initializeRound();

        if ($this->turn == 'bot') {
            $this->handleBotTurn();
        }
    }

    public function initializeRound()
    {
        $this->userTower = $this->generateTower();
        $this->botTower = $this->generateTower();
        $this->userCurrentRow = 0;
        $this->botCurrentRow = 0;
        $this->userRowStates = [];
        $this->botRowStates = [];
        $this->playersTurnsTaken = [];
        $this->progress[$this->currentRound] = ['user' => 0, 'bot' => 0];
        $this->turn = $this->firstPlayer;
        Log::debug('Round ' . $this->currentRound . ' initialized. Turn: ' . $this->turn);

        if ($this->turn == 'bot') {
            $this->handleBotTurn();
        }
    }

    private function generateTower()
    {
        $tower = [];
        for ($row = 0; $row < 5; $row++) {
            $correctIndex = rand(0, 1);
            $tower[$row] = [
                0 => ['correct' => $correctIndex == 0, 'selected' => false, 'wrong' => false],
                1 => ['correct' => $correctIndex == 1, 'selected' => false, 'wrong' => false],
            ];
        }
        return $tower;
    }

    public function selectTicket($player, $row, $ticketIndex)
    {
        if ($this->gameWinner || $this->turn != $player || $this->{$player . 'CurrentRow'} != $row) {
            return;
        }

        $tower = $player . 'Tower';
        $rowStatesProp = $player . 'RowStates';

        if (!isset($this->$rowStatesProp[$row])) {
            $this->$rowStatesProp[$row] = false;
        }

        $this->$tower[$row][$ticketIndex]['selected'] = true;

        if ($this->$tower[$row][$ticketIndex]['correct']) {
            $this->$tower[$row][0]['selected'] = true;
            $this->$tower[$row][1]['selected'] = true;
            $this->$rowStatesProp[$row] = true;
            $this->{$player . 'CurrentRow'}++;

            if ($this->{$player . 'CurrentRow'} == 5) {
                $this->endTurn($player);
                return;
            }
            if ($player == 'bot') {
                $this->dispatch('bot-move', ['continue' => true]);
            }
        } else {
            $this->$tower[$row][$ticketIndex]['wrong'] = true;
            $this->$rowStatesProp[$row] = false;
            $this->endTurn($player);
        }
    }

    private function botSelectTicket()
    {
        if ($this->gameWinner || $this->turn != 'bot') {
            return false;
        }

        $row = $this->botCurrentRow;
        $knowsCorrect = rand(0, 99) < 20;
        $ticketIndex = $knowsCorrect ? array_search(true, array_column($this->botTower[$row], 'correct')) : rand(0, 1);

        $this->selectTicket('bot', $row, $ticketIndex);
        return $this->botCurrentRow < 5 && $this->botTower[$row][$ticketIndex]['correct'] && !$this->gameWinner;
    }

    public function handleBotTurn()
    {
        $this->botSelectTicket();
    }

    private function endTurn($player)
    {
        $this->progress[$this->currentRound][$player] = $this->{$player . 'CurrentRow'};
        $this->playersTurnsTaken[] = $player;
        $this->turn = ($player == 'user') ? 'bot' : 'user';
        Log::debug('Turn ended for ' . $player . '. Next turn: ' . $this->turn);

        if ($this->turn == 'bot') {
            $this->handleBotTurn();
        }

        if (count($this->playersTurnsTaken) == 2) {
            $this->dispatch('end-round');
        }
    }

    public function endRound()
    {
        $userProgress = $this->progress[$this->currentRound]['user'];
        $botProgress = $this->progress[$this->currentRound]['bot'];
        $this->userScore += $userProgress;
        $this->botScore += $botProgress;

        if ($this->currentRound == 20) {
            $this->gameWinner = $this->userScore > $this->botScore ? 'user' : ($this->botScore > $this->userScore ? 'bot' : 'tie');
        } else {
            $this->currentRound++;
            $this->initializeRound();
        }
    }

    public function render()
    {
        return view('livewire.battlefield-game');
    }
}
