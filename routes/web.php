<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/img/akun/{filename}', function($filename) {
    $path1 = storage_path('app/public/akun/' . $filename);
    $path2 = public_path('images/akun/' . $filename);
    
    if (file_exists($path1)) {
        return Response::file($path1);
    }
    if (file_exists($path2)) {
        return Response::file($path2);
    }
    abort(404);
});

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');