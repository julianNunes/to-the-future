<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'eu_dinovu@hotmail.com')->first();
echo "User exists: " . ($user ? "yes" : "no") . PHP_EOL;
if ($user) {
    echo "Password matches: " . (Illuminate\Support\Facades\Hash::check('password', $user->password) ? "yes" : "no") . PHP_EOL;
    echo "User count: " . App\Models\User::count() . PHP_EOL;
}
