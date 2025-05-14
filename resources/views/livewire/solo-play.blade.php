<div class="soloplayer">
    <div class="container">
        <div class="inner py-5">
            <div class="game-box">
                <div class="game-box-head text-center">
                    <h2 class="mb-3">Ticket Tower-Solo</h2>
                    <h3 class="mb-3 fs-3">Win Free $50 Cash!</h3>
                    <p class="mb-2">GIVEAWAY ID: GLBDJVG6</p>
                    <div class="d-flex align-items-center justify-content-center mb-4 gap-2">
                        <img src="{{ asset('img/awatar.png') }}" class=" rounded-full object-cover" alt="Meet Maba logo">
                        <p class="fs-6">Meet Maba</p>
                    </div>
                </div>
                <div class="grid-wrapper d-flex flex-column justify-content-center align-items-center">
                    <button class="p-3 w-100 rounded mb-6 flex items-center justify-center my-3">
                        Play to win entries in the giveaway!
                    </button>
                    <div class="tile-grid w-100">
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
        </div>
        <!-- Audio Elements -->
        <audio id="correct-sound" src="{{ asset('sounds/correct-tiles.wav') }}"></audio>
        <audio id="wrong-sound" src="{{ asset('sounds/wrong-tile.wav') }}"></audio>
        <audio id="play-sound" src="{{ asset('sounds/play.wav') }}"></audio>
    </div>
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
