<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RuleEngineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(
        \App\Services\RuleEngineService $ruleEngineService,
        \App\Services\FeedbackEngineService $feedbackEngineService
    ): void {
        // 1. Create Demo User
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        // Ensure we have some basic categories
        $food = Category::where('name', 'Food')->first() ?? Category::create(['name' => 'Food', 'type' => 'expense', 'user_id' => $user->id]);
        $shopping = Category::where('name', 'Shopping')->first() ?? Category::create(['name' => 'Shopping', 'type' => 'expense', 'user_id' => $user->id]);
        $rent = Category::where('name', 'Rent')->first() ?? Category::create(['name' => 'Rent', 'type' => 'expense', 'user_id' => $user->id]);

        // Clear existing data for this user for a clean demo
        Transaction::where('user_id', $user->id)->delete();
        \App\Models\FeedbackHistory::where('user_id', $user->id)->delete();
        \App\Models\UserProgress::where('user_id', $user->id)->delete();

        // 2. Setup History (Last 4 weeks)
        $now = Carbon::now();

        for ($i = 4; $i >= 0; $i--) {
            $targetDate = $now->clone()->subWeeks($i);
            $monday = $targetDate->clone()->startOfWeek(Carbon::MONDAY);

            // Generate varied data based on the week index to simulate behavioral changes
            if ($i === 4) {
                // Baseline week (Low spending)
                $this->createTransactions($user->id, $food->id, 50, $monday->clone()->addDay());
                $this->createTransactions($user->id, $rent->id, 500, $monday->clone());
            } elseif ($i === 3) {
                // Spike week (Triggers Weekly Spike & Category Overspend)
                $this->createTransactions($user->id, $food->id, 150, $monday->clone()->addDay()); // Food spike
                $this->createTransactions($user->id, $shopping->id, 200, $monday->clone()->addDays(2)); // Total spike
            } elseif ($i === 2) {
                // Improvement week (Lower spending)
                $this->createTransactions($user->id, $food->id, 80, $monday->clone()->addDay());
            } elseif ($i === 1) {
                // Small purchases week (Triggers frequent small purchases)
                $this->createSmallTransactions($user->id, $food->id, $monday, 12);
            } else {
                // Current week (Mixed patterns)
                $this->createTransactions($user->id, $food->id, 200, $monday->clone()->addDay());
                $this->createSmallTransactions($user->id, $shopping->id, $monday, 12);
            }

            // After seeding the week's transactions, evaluate and generate feedback "for that week"
            $evaluation = $ruleEngineService->evaluateRules($user->id, $targetDate);
            $feedbackEngineService->processRuleResults($evaluation);
        }

        $this->command->info('RuleEngineSeeder: 5 weeks of history generated for demo@example.com with adaptive feedback.');
    }

    private function createTransactions(int $userId, int $categoryId, float $amount, Carbon $date): void
    {
        Transaction::create([
            'type' => 'expense',
            'amount' => $amount,
            'date' => $date->toDateString(),
            'category_id' => $categoryId,
            'user_id' => $userId,
            'description' => 'Simulated Transaction',
        ]);
    }

    private function createSmallTransactions(int $userId, int $categoryId, Carbon $monday, int $count): void
    {
        $smallItems = ['Coffee', 'Snack', 'Bus', 'Quick Bite'];
        for ($k = 0; $k < $count; $k++) {
            Transaction::create([
                'type' => 'expense',
                'amount' => rand(300, 800) / 100, // $3 - $8
                'date' => $monday->clone()->addDays(rand(0, 4))->toDateString(),
                'category_id' => $categoryId,
                'user_id' => $userId,
                'description' => $smallItems[array_rand($smallItems)],
            ]);
        }
    }
}
