<?php

use Illuminate\Support\Facades\Route;
use Modules\WebsiteBase\app\Http\Controllers\Auth\AuthenticatedSessionController;

Route::middleware('guest')->group(function () {
    Route::get('register', function () {
        return view('website-base::page', [
            'title'       => __('Register'),
            'contentView' => 'auth.register',
        ]);
    })->name('register');


    Route::get('login', function () {
        return view('website-base::page', [
            'title'       => __('Login'),
            'contentView' => 'auth.login',
        ]);
    })->name('login');

    // Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', function () {
        return view('website-base::page', [
            'title'       => __('Forgot your password?'),
            'contentView' => 'auth.forgot-password',
        ]);
    })->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
