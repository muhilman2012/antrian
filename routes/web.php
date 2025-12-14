<?php

use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QueueController::class, 'index'])->name('registration');
Route::post('/register', [QueueController::class, 'store'])->name('queue.store');

Route::get('/admin', [QueueController::class, 'admin'])->name('admin');
Route::post('/call/{id}', [QueueController::class, 'call'])->name('queue.call');
Route::post('/complete/{id}', [QueueController::class, 'complete'])->name('queue.complete');
Route::get('/api/next-number/{category}', [QueueController::class, 'getNextNumber']);

Route::get('/display', [QueueController::class, 'display'])->name('display');
Route::get('/api/current-queue', [QueueController::class, 'getCurrentQueue'])->name('api.current.queue');