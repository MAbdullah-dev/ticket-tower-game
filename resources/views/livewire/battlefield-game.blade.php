<div class="container mt-4 battlefield-game">
    <h3>Player vs Bot Ticket Tower</h3>

    <div class="mb-2">
        Tickets: {{ $tickets }} | Round: {{ $roundsPlayed }}/20
    </div>
    <div class="mb-2">
        Turn: <strong>{{ strtoupper($activePlayer) }}</strong>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @elseif (session()->has('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="d-flex gap-5">
        <!-- User Tower -->
        <div>
            <h5>You</h5>
            <div class="tile-grid">
                @foreach (array_chunk($userTiles, 2) as $rowIndex => $tileRow)
                    @foreach ($tileRow as $index => $tile)
                        @php
                            $tileIndex = $rowIndex * 2 + $index;
                            $revealed = in_array($tileIndex, $userRevealed);
                        @endphp
                        <button class="tile {{ $tile == 'ticket' ? 'correct' : ($tile == 'empty' ? 'wrong' : '') }}"
                            wire:click="revealTile({{ $tileIndex }})"
                            {{ $revealed || $activePlayer != 'user' ? 'disabled' : '' }}>
                            <i class="fa-solid fa-ticket"></i>
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- Bot Tower -->
        <div>
            <h5>Bot</h5>
            <div class="tile-grid">
                @foreach (array_chunk($botTiles, 2) as $rowIndex => $tileRow)
                    @foreach ($tileRow as $index => $tile)
                        @php
                            $tileIndex = $rowIndex * 2 + $index;
                            $revealed = in_array($tileIndex, $botRevealed);
                        @endphp
                        <button class="tile {{ $tile == 'ticket' ? 'correct' : ($tile == 'empty' ? 'wrong' : '') }}"
                            disabled>
                            <i class="fa-solid fa-robot"></i>
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <button wire:click="startNewGame" class="btn btn-primary" data-sound="play">Start New Game</button>
    </div>

    <!-- Sounds -->
    <audio id="correct-sound" src="{{ asset('sounds/correct-tiles.wav') }}"></audio>
    <audio id="wrong-sound" src="{{ asset('sounds/wrong-tile.wav') }}"></audio>
    <audio id="play-sound" src="{{ asset('sounds/play.wav') }}"></audio>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('play-sound', ({
            sound
        }) => {
            const audio = document.getElementById($ {
                sound
            } - sound);
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
        });
    });
</script>
