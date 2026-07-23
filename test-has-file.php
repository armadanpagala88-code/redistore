<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/test', 'POST', [], [], [
    'gambar_utama' => [
        new \Illuminate\Http\UploadedFile(__FILE__, 'test.php', 'text/plain', null, true)
    ]
]);

echo "hasFile('gambar_utama'): " . ($request->hasFile('gambar_utama') ? 'true' : 'false') . "\n";
echo "hasFile('gambar_utama.0'): " . ($request->hasFile('gambar_utama.0') ? 'true' : 'false') . "\n";
