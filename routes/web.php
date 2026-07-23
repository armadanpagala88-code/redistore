<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/img/{folder}/{filename}', function($folder, $filename) {
    $path1 = storage_path('app/public/' . $folder . '/' . $filename);
    $path2 = public_path('images/' . $folder . '/' . $filename);
    $path3 = public_path('uploads/' . $folder . '/' . $filename);
    
    if (file_exists($path1)) {
        return Response::file($path1);
    }
    if (file_exists($path2)) {
        return Response::file($path2);
    }
    if (file_exists($path3)) {
        return Response::file($path3);
    }
    abort(404);
});

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');