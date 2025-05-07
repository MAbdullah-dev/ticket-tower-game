<div class="container mt-5" style="max-width: 400px;">
    <div class="card bg-dark text-white p-4 border border-white rounded" style="border-radius: 15px;">
        <h4 class="text-center mb-4">Play to win entries in the giveaway!</h4>

        <div class="row g-2">
            @for ($i = 0; $i < 8; $i++)
                <div class="col-6">
                    <button wire:click="pickTicket({{ $i % 2 }})" class="btn btn-outline-light border rounded py-3 w-100" style="height: 60px; background-color: #333; border-color: #444;">
                        <img src="https://cdn-icons-png.flaticon.com/512/2890/2890773.png" alt="Ticket" style="height: 24px;">
                    </button>
                </div>
            @endfor
        </div>

        <button class="btn btn-success w-100 mt-4 py-2" style="font-weight: bold;">Playing</button>
        <p class="text-center mt-3">You have {{ $tickets }} ticket(s)</p>
    </div>
</div>
