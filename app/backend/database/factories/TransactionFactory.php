<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['income', 'expense']);

        // Get or create a category of the matching type
        $category = Category::where('type', $type)->first();

        if (! $category) {
            $category = Category::create([
                'name' => $type === 'income' ? 'Salary' : 'General',
                'type' => $type,
            ]);
        }

        return [
            'type' => $type,
            'amount' => $this->faker->randomFloat(2, 1, 10000),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'category_id' => $category->id,
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the transaction is an income.
     */
    public function income(): static
    {
        return $this->state(function (array $attributes) {
            $category = Category::where('type', 'income')->first();

            if (! $category) {
                $category = Category::create([
                    'name' => 'Salary',
                    'type' => 'income',
                ]);
            }

            return [
                'type' => 'income',
                'category_id' => $category->id,
            ];
        });
    }

    /**
     * Indicate that the transaction is an expense.
     */
    public function expense(): static
    {
        return $this->state(function (array $attributes) {
            $category = Category::where('type', 'expense')->first();

            if (! $category) {
                $category = Category::create([
                    'name' => 'General',
                    'type' => 'expense',
                ]);
            }

            return [
                'type' => 'expense',
                'category_id' => $category->id,
            ];
        });
    }
}
