<?php

return [

    /*
    | PIN akses aplikasi. Kosong = tanpa PIN, hanya diizinkan di luar production.
    | Di production, PIN yang kosong membuat aplikasi tidak bisa dipakai.
    */
    'pin' => env('APP_PIN'),

    /*
    | Menit tanpa aktivitas (klik/keyboard) sebelum aplikasi kembali ke dashboard dan minta PIN lagi.
    */
    'idle_minutes' => (int) env('APP_IDLE_MINUTES', 30),

];
