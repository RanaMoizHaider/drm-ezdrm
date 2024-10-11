<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('video.index');
Route::get('/upload', [VideoController::class, 'showUploadForm'])->name('video.upload.form');
Route::post('/upload', [VideoController::class, 'uploadVideo'])->name('video.upload');
Route::get('/play/{videoID}', [VideoController::class, 'playVideo'])->name('video.play');
