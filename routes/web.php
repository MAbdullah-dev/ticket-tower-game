<?php

use App\Livewire\BattlefieldGame;
use App\Livewire\SoloPlay;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', SoloPlay::class)->name('soloPlay');
Route::get('/battlefield', BattlefieldGame::class)->name('battlefield');
// routes/web.php
Route::get('/test-chance', function () {
    $wins = 0;
    $trials = 10000;

    for ($i = 0; $i < $trials; $i++) {
        if (mt_rand(1, 100) <= 40) {
            $wins++;
        }
    }

    $percentage = ($wins / $trials) * 100;
    return "Out of $trials trials, wins = $wins (~" . round($percentage, 2) . "%)";
});
