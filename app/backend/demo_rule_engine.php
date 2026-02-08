<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RuleEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== Rule Engine Demonstration ===\n\n";

DB::beginTransaction();

try {
    // 1. Setup User and Categories
    $user = User::first() ?? User::factory()->create();
    $food = Category::where('name', 'Food')->first() ?? Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $user->id]);

    echo "Using User: {$user->email}\n";

    // Clear recent transactions for demo
    Transaction::where('user_id', $user->id)->delete();

    // 2. Setup Scenarios
    $today = Carbon::parse('2026-01-20'); // A Tuesday

    echo 'Evaluation Date: '.$today->toDateString()." (Tuesday)\n";
    echo "Current Week: Jan 19 - Jan 25\n";
    echo "Previous Week: Jan 12 - Jan 18\n\n";

    // Scenario A: Category Overspend (Food goes from 100 to 150)
    Transaction::create(['type' => 'expense', 'amount' => 100, 'date' => '2026-01-15', 'category_id' => $food->id, 'user_id' => $user->id]);
    Transaction::create(['type' => 'expense', 'amount' => 150, 'date' => '2026-01-20', 'category_id' => $food->id, 'user_id' => $user->id]);
    echo "✓ Scenario A: Food spending increased 50% WoW.\n";

    // Scenario B: Frequent Small Purchases (12 transactions @ $5)
    for ($i = 0; $i < 12; $i++) {
        Transaction::create(['type' => 'expense', 'amount' => 5, 'date' => '2026-01-21', 'category_id' => $food->id, 'user_id' => $user->id]);
    }
    echo "✓ Scenario B: 12 small purchases (<$10) added.\n";

    // 3. Evaluate
    echo "\nEvaluating Rules...\n";
    $service = app(RuleEngineService::class);
    $results = $service->evaluateRules($user->id, $today);

    // 4. Output Results
    echo "\nTriggered Rules:\n";
    echo "----------------\n";

    if (empty($results['triggered_rules'])) {
        echo "No rules triggered.\n";
    } else {
        foreach ($results['triggered_rules'] as $rule) {
            echo "[!] {$rule['rule_id']}\n";
            echo '    Details: '.json_encode($rule['data'], JSON_PRETTY_PRINT)."\n\n";
        }
    }

} catch (\Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
} finally {
    DB::rollBack();
    echo "Database rolled back. Demo finished.\n";
}
