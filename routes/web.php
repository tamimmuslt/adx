<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {
    Mail::raw('Hello from Laravel using Gmail SMTP!', function ($message) {
        $message->to('tamimmslt5@gmail.com')
                ->subject('Test Email from Laravel');
    });

    return 'Mail sent!';
});
