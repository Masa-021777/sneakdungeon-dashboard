<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'dashboard')->name('dashboard');

// ランキング画面を作るまではAPIのJSONを表示する
Route::redirect('/rankings', '/api/rankings')->name('rankings');

Route::redirect('/export', '/api/export')->name('export');
