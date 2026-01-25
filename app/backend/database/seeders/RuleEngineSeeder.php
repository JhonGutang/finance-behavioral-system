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
    public function run(): void
    {
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

        // Clear existing transactions for this user for a clean demo
        Transaction::where('user_id', $user->id)->delete();

        // 2. Setup Weeks
        // Assuming current date is Tuesday 2026-01-20
        $today = Carbon::parse('2026-01-20');
        $currentWeekMonday = $today->clone()->startOfWeek(Carbon::MONDAY);
        $previousWeekMonday = $currentWeekMonday->clone()->subWeek();

        // --- PREVIOUS WEEK (Baseline) ---
        // Stable spending patterns
        Transaction::create([
            'type' => 'expense', 'amount' => 100.00, 'date' => $previousWeekMonday->clone()->addDays(2)->toDateString(), // Wednesday
            'category_id' => $food->id, 'user_id' => $user->id, 'description' => 'Weekly Groceries'
        ]);

        Transaction::create([
            'type' => 'expense', 'amount' => 500.00, 'date' => $previousWeekMonday->clone()->toDateString(), // Monday
            'category_id' => $rent->id, 'user_id' => $user->id, 'description' => 'Monthly Rent'
        ]);

        // Total Previous: $600

        // --- CURRENT WEEK (The Spike) ---

        // Triggers Rule 1: Category Overspend (Food goes from $100 -> $160, +60%)
        Transaction::create([
            'type' => 'expense', 'amount' => 160.00, 'date' => $currentWeekMonday->clone()->toDateString(), // Monday
            'category_id' => $food->id, 'user_id' => $user->id, 'description' => 'Bulk Grocery Run'
        ]);

        // Triggers Rule 2: Weekly Spending Spike (Total goes from $600 last week to $1000+ this week)
        Transaction::create([
            'type' => 'expense', 'amount' => 800.00, 'date' => $currentWeekMonday->clone()->addDay()->toDateString(), // Tuesday
            'category_id' => $shopping->id, 'user_id' => $user->id, 'description' => 'New Laptop'
        ]);

        // Triggers Rule 3: Frequent Small Purchases (12 small transactions)
        $smallItems = ['Morning Coffee', 'Afternoon Snack', 'Evening Cookie', 'Parking Fee', 'Bus Ticket'];
        for ($i = 0; $i < 12; $i++) {
            Transaction::create([
                'type' => 'expense', 
                'amount' => rand(300, 800) / 100, // $3.00 to $8.00
                'date' => $currentWeekMonday->clone()->addDays(rand(0, 2))->toDateString(), // Mon-Wed
                'category_id' => $food->id, 
                'user_id' => $user->id, 
                'description' => $smallItems[array_rand($smallItems)] . " #$i"
            ]);
        }

        $this->command->info('RuleEngineSeeder: Demo user demo@example.com created with behavioral patterns.');
    }
}
