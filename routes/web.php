<?php

use App\Livewire\SoloPlay;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/',SoloPlay::class)->name('soloPlay');
