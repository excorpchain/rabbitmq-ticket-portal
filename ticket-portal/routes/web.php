<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;

Route::get('/', [EventController::class, 'index'])
    ->name('events.index');

Route::post('/orders', [OrderController::class, 'store'])
    ->name('orders.store');

Route::get('/orders', [OrderController::class, 'index'])
    ->name('orders.index');

Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');

Route::get('/orders/{order}/ticket', [
    OrderController::class,
    'downloadTicket'
])->name('orders.ticket.download');
