<?php

use Illuminate\Support\Facades\Route;
use RoundlyConsulting\Appointments\Models\Appointment;

Route::get('/agenda', function () {
$appointments = Appointment::query()
    ->whereBetween('starts_at', [
        now()->startOfMonth(),
        now()->endOfMonth(),
    ])
    ->orderBy('starts_at')
    ->get();


    return view('agenda', compact('appointments'));
});
