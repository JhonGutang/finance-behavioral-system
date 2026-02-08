<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AnalyticsService;

$service = app(AnalyticsService::class);

echo "=== Testing Fixed Analytics Service ===\n\n";

echo "Daily Analytics (30 days):\n";
$dailyData = $service->getDailyFlow(30);
echo 'Labels count: '.count($dailyData['labels'])."\n";
echo 'Last label: '.end($dailyData['labels'])."\n";
echo 'Income for last day: '.end($dailyData['income'])."\n";
echo 'Expenses for last day: '.end($dailyData['expenses'])."\n\n";

echo "Monthly Analytics (12 months):\n";
$monthlyData = $service->getMonthlyFlow(12);
echo 'Labels count: '.count($monthlyData['labels'])."\n";
echo 'Last label: '.end($monthlyData['labels'])."\n";
echo 'Income for last month: '.end($monthlyData['income'])."\n";
echo 'Expenses for last month: '.end($monthlyData['expenses'])."\n";
