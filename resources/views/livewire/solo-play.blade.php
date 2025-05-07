<div class="container">
    <div class="game-box">
        <div class="game-box-head">
            Play to Win Entries in the Giveaway!
        </div>

        <div class="tile-grid">
            @foreach (array_chunk($tiles, 2) as $rowIndex => $tileRow)
                @foreach ($tileRow as $index => $tile)
                    @php
                        $tileIndex = $rowIndex * 2 + $index;
                        $isRevealed = in_array($tileIndex, $revealedTiles);
                        $isCorrect = $tile === 'ticket';
                        $isWrong = $tile === 'empty';
                    @endphp

                    <button class="tile {{ $isRevealed ? 'correct' : '' }} {{ $isWrong ? 'wrong' : '' }}"
                        wire:click="revealTile({{ $tileIndex }})"
                        data-sound="{{ $isRevealed ? 'correct' : ($tile == 'ticket' ? 'correct' : 'wrong') }}"
                        {{ in_array($tileIndex, $revealedTiles) || $tile === 'empty' ? 'disabled' : '' }}>
                        <i class="fa-solid fa-ticket"></i>
                    </button>
                @endforeach
            @endforeach
        </div>

        <div class="playing-btn" wire:click="initializeGame" data-sound="play">
            {{ $gameActive ? 'Playing' : 'Play' }}
        </div>


        <div class="ticket-count">
            YOU HAVE {{ $tickets }} TICKET(S)
        </div>

        @if (session()->has('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        @if (session()->has('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        @if (session()->has('info'))
            <div class="alert alert-info mt-3">{{ session('info') }}</div>
        @endif
    </div>
    <!-- Audio Elements -->
    <audio id="correct-sound" src="{{ asset('sounds/correct-tiles.wav') }}"></audio>
    <audio id="wrong-sound" src="{{ asset('sounds/wrong-tile.wav') }}"></audio>
    <audio id="play-sound" src="{{ asset('sounds/play.wav') }}"></audio>
</div>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('play-sound', ({
            sound
        }) => {
            const audio = document.getElementById(`${sound}-sound`);
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(err => {
                    console.warn(`Sound ${sound} blocked:`, err);
                });
            } else {
                console.warn(`Audio element for sound ${sound} not found`);
            }
        });
    });
</script>
