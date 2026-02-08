<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use Carbon\Carbon;

echo "=== Testing Analytics Date Logic ===\n\n";

// Get all transactions
$allTransactions = Transaction::all();
echo 'Total transactions: '.$allTransactions->count()."\n";
foreach ($allTransactions as $txn) {
    echo "  - ID {$txn->id}: {$txn->type} \${$txn->amount} on {$txn->date}\n";
}

echo "\n--- Daily Analytics Logic ---\n";
$days = 30;
$endDate = Carbon::today();
$startDate = Carbon::today()->subDays($days - 1);

echo 'End Date (today): '.$endDate->format('Y-m-d')."\n";
echo 'Start Date: '.$startDate->format('Y-m-d')."\n";

$transactions = Transaction::whereBetween('date', [
    $startDate->format('Y-m-d'),
    $endDate->format('Y-m-d'),
])->get();

echo 'Transactions in range: '.$transactions->count()."\n";
foreach ($transactions as $txn) {
    echo "  - ID {$txn->id}: {$txn->type} \${$txn->amount} on {$txn->date}\n";
}

// Check date parsing
echo "\n--- Date Parsing Test ---\n";
foreach ($allTransactions as $txn) {
    $parsed = Carbon::parse($txn->date);
    echo "Transaction {$txn->id} date: {$txn->date}\n";
    echo '  Parsed: '.$parsed->format('Y-m-d')."\n";
    echo '  Is between? '.($parsed->between($startDate, $endDate) ? 'YES' : 'NO')."\n";
}
