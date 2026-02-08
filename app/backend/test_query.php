<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use Carbon\Carbon;

echo "=== Testing Different Query Methods ===\n\n";

$days = 30;
$endDate = Carbon::today();
$startDate = Carbon::today()->subDays($days - 1);

echo "Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n\n";

// Method 1: whereBetween with date strings
echo "Method 1: whereBetween with date strings\n";
$result1 = Transaction::whereBetween('date', [
    $startDate->format('Y-m-d'),
    $endDate->format('Y-m-d'),
])->get();
echo 'Count: '.$result1->count()."\n\n";

// Method 2: whereDate
echo "Method 2: whereDate with >= and <=\n";
$result2 = Transaction::whereDate('date', '>=', $startDate->format('Y-m-d'))
    ->whereDate('date', '<=', $endDate->format('Y-m-d'))
    ->get();
echo 'Count: '.$result2->count()."\n\n";

// Method 3: Raw date comparison
echo "Method 3: whereRaw with DATE()\n";
$result3 = Transaction::whereRaw('DATE(date) >= ?', [$startDate->format('Y-m-d')])
    ->whereRaw('DATE(date) <= ?', [$endDate->format('Y-m-d')])
    ->get();
echo 'Count: '.$result3->count()."\n";
