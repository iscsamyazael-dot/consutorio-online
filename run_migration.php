<?php
chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
exit($kernel->handle(
    $input = new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
        '--no-interaction' => true,
    ]),
    new \Symfony\Component\Console\Output\ConsoleOutput()
));
