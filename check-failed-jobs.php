<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$failedJobs = DB::table('failed_jobs')->latest('failed_at')->get();

foreach ($failedJobs as $job) {
    echo "ID: {$job->id}\n";
    echo "Failed At: {$job->failed_at}\n";
    echo "Exception:\n";
    echo substr($job->exception, 0, 500) . "...\n";
    echo str_repeat('-', 80) . "\n";
}