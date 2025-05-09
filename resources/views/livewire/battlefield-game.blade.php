<div>
    <h1>Ticket Tower Battle</h1>
    <p>Tickets: {{ $tickets }}</p>
    @if (!$gameStarted)
        <button wire:click="startGame" class="btn btn-primary" @if ($tickets < 10) disabled @endif>Start Game (10 tickets)</button>
    @else
        <div class="row">
            <div class="col-md-6">
                <h2>Your Tower</h2>
                @foreach (array_reverse($userTower) as $rowIndex => $row)
                    <div class="d-flex justify-content-center mb-2 {{ collect($row)->some(fn($t) => $t['selected'] && $t['correct']) ? 'bg-success' : '' }}">
                        @foreach ($row as $ticketIndex => $ticket)
                            <button 
                                wire:click="selectTicket('user', {{ 4 - $rowIndex }}, {{ $ticketIndex }})"
                                class="btn {{ $ticket['selected'] ? ($ticket['correct'] ? 'btn-success' : 'btn-danger') : 'btn-secondary' }} mx-1"
                                @if ($turn != 'user' || $userCurrentRow != (4 - $rowIndex)) disabled @endif
                            >
                                Ticket {{ $ticketIndex + 1 }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="col-md-6">
                <h2>Bot Tower</h2>
                @foreach (array_reverse($botTower) as $rowIndex => $row)
                    <div class="d-flex justify-content-center mb-2 {{ collect($row)->some(fn($t) => $t['selected'] && $t['correct']) ? 'bg-success' : '' }}">
                        @foreach ($row as $ticketIndex => $ticket)
                            <button 
                                class="btn {{ $ticket['selected'] ? ($ticket['correct'] ? 'btn-success' : 'btn-danger') : 'btn-secondary' }} mx-1"
                                disabled
                            >
                                Ticket {{ $ticketIndex + 1 }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        <p>Round: {{ $currentRound }}/20</p>
        <p>Turn: {{ $turn == 'user' ? 'Your Turn' : 'Bot\'s Turn' }}</p>
        @if ($gameWinner)
            <p>{{ $gameWinner == 'user' ? 'You Win!' : ($gameWinner == 'bot' ? 'Bot Wins!' : 'It\'s a Tie!') }}</p>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Round</th>
                    <th>Your Correct</th>
                    <th>Bot Correct</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($progress as $round => $data)
                    <tr>
                        <td>{{ $round }}</td>
                        <td>{{ $data['user'] }}</td>
                        <td>{{ $data['bot'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Total</td>
                    <td>{{ $userScore }}</td>
                    <td>{{ $botScore }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

<script>
    console.log('Battlefield game script loaded');

    function waitForLivewire(callback) {
        if (typeof Livewire !== 'undefined') {
            console.log('Livewire is available');
            callback();
        } else {
            console.log('Livewire not yet available, waiting...');
            setTimeout(() => waitForLivewire(callback), 100);
        }
    }

    function waitForComponent(componentId, callback) {
        const $wire = Livewire.find(componentId);
        if ($wire) {
            console.log('Component found for ID:', componentId);
            callback($wire);
        } else {
            console.log('Component not yet available for ID:', componentId, 'waiting...');
            setTimeout(() => waitForComponent(componentId, callback), 100);
        }
    }

    waitForLivewire(() => {
        console.log('Setting up Livewire event listeners');

        Livewire.on('botMove', (event) => {
            console.log('BotMove event received with data:', event);

            const componentId = event.component.id;
            const shouldContinue = event.continue;

            waitForComponent(componentId, ($wire) => {
                const turn = $wire.get('turn');
                const botCurrentRow = $wire.get('botCurrentRow');
                console.log('BotMove processed. Continue:', shouldContinue, 'Turn:', turn, 'Bot Current Row:', botCurrentRow);

                if (shouldContinue) {
                    console.log('Scheduling botTurn event dispatch after 1-second delay');
                    setTimeout(() => {
                        console.log('Dispatching botTurn event for next bot move');
                        try {
                            window.Livewire.emit('botTurn');
                        } catch (error) {
                            console.error('Error dispatching botTurn:', error);
                        }
                    }, 1000); // 1-second delay before next bot move
                } else {
                    console.log('Bot turn ended. Waiting for user turn or round end.');
                }
            });
        });
    });
</script>