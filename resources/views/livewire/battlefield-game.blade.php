<div class="battlefield-game">
    <div class="container">
        <div class="inner py-5">
            <h1 class="text-center">Ticket Tower Battlefield</h1>

            <div class="d-flex my-5 justify-content-center flex-column align-items-center">
                <button wire:click="startGame" class="btn btn-primary px-5 py-3"
                    @if ($tickets < 10 || $gameStarted) disabled @endif>
                    Start Game (10 tickets)
                </button>

                <h5 class="mt-4"><strong>Round: {{ $currentRound }}/20</strong></h5>
                <p>Turn: {{ $turn == 'user' ? 'Your Turn' : 'Bot\'s Turn' }}</p>

                @if ($gameWinner)
                    <p class="fw-bold text-success">
                        {{ $gameWinner == 'user' ? 'You Win!' : ($gameWinner == 'bot' ? 'Bot Wins!' : 'It\'s a Tie!') }}
                    </p>
                @endif
            </div>

            <div class="row">
                {{-- USER TOWER --}}
                <div class="col-6">
                    <h2 class="text-center">Your Tower</h2>
                    <h6 class="text-center">Score: {{ $userScore }}</h6>
                    <div class="tower-border">
                        @foreach (array_reverse($userTower) as $rowIndex => $row)
                            @php $actualRow = 4 - $rowIndex; @endphp
                            <div class="tile-grid text-center my-1">
                                @foreach ($row as $ticketIndex => $ticket)
                                    <button wire:click="selectTicket('user', {{ $actualRow }}, {{ $ticketIndex }})"
                                        class="tile mx-1
                            @if ($ticket['selected']) @if ($userRowStates[$actualRow] ?? false) correct
                                @elseif ($ticket['wrong']) wrong
                                @elseif ($ticket['correct']) correct @endif
@else
btn-secondary
                            @endif"
                                        @if ($turn !== 'user' || $userCurrentRow !== $actualRow || $ticket['selected']) disabled @endif>
                                        <i class="fa-solid fa-ticket"></i>
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- BOT TOWER --}}
                <div class="col-6">
                    <h2 class="text-center">Bot Tower</h2>
                    <h6 class="text-center">Score: {{ $botScore }}</h6>
                    <div class="tower-border">
                        @foreach (array_reverse($botTower) as $rowIndex => $row)
                            @php $actualRow = 4 - $rowIndex; @endphp
                            <div class="tile-grid text-center my-1">
                                @foreach ($row as $ticketIndex => $ticket)
                                    <button
                                        class="tile mx-1
                            @if ($ticket['selected']) @if ($botRowStates[$actualRow] ?? false) correct
                                @elseif ($ticket['wrong']) wrong
                                @elseif ($ticket['correct']) correct @endif
@else
btn-secondary
                            @endif"
                                        disabled>
                                        <i class="fa-solid fa-ticket"></i>
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bot event logic --}}
<script>
    function waitForLivewire(callback) {
        if (typeof Livewire !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForLivewire(callback), 100);
        }
    }

    waitForLivewire(() => {
        window.addEventListener('bot-move', (event) => {
            const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            const shouldContinue = data?.continue || false;

            if (shouldContinue) {
                setTimeout(() => {
                    window.Livewire.dispatch('botTurn');
                }, 1500);
            }
        });
        window.addEventListener('end-round', () => {
            setTimeout(() => {
                window.Livewire.dispatch('endRound');
            }, 1000);
        });
    });
</script>
