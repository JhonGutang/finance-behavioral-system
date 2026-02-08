<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use Carbon\Carbon;

echo "=== Debugging Daily Flow Logic ===\n\n";

$days = 30;
$endDate = Carbon::today();
$startDate = Carbon::today()->subDays($days - 1);

echo 'Today: '.Carbon::today()->format('Y-m-d')."\n";
echo 'Start Date: '.$startDate->format('Y-m-d')."\n";
echo 'End Date: '.$endDate->format('Y-m-d')."\n\n";

// Get transactions
$transactions = Transaction::whereDate('date', '>=', $startDate->format('Y-m-d'))
    ->whereDate('date', '<=', $endDate->format('Y-m-d'))
    ->get();

echo 'Transactions found: '.$transactions->count()."\n";
foreach ($transactions as $txn) {
    echo "  - {$txn->date} | {$txn->type} | \${$txn->amount}\n";
}

// Group by date
echo "\nGrouping transactions by date:\n";
$grouped = $transactions->groupBy(function ($transaction) {
    return Carbon::parse($transaction->date)->format('Y-m-d');
});

foreach ($grouped as $date => $dateTransactions) {
    echo "Date: $date\n";
    $transactionDate = Carbon::parse($date);
    $dayIndex = $transactionDate->diffInDays($startDate);
    echo "  Day index: $dayIndex\n";

    $incomeSum = $dateTransactions->where('type', 'income')->sum('amount');
    $expenseSum = $dateTransactions->where('type', 'expense')->sum('amount');
    echo "  Income: \${$incomeSum}\n";
    echo "  Expenses: \${$expenseSum}\n";
}
