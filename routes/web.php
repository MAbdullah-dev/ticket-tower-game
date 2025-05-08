<?php

use App\Livewire\BattlefieldGame;
use App\Livewire\SoloPlay;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', SoloPlay::class)->name('soloPlay');
Route::get('/battlefield', BattlefieldGame::class)->name('battlefield');
