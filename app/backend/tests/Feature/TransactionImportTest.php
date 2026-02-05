<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransactionImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_preview_csv_import()
    {
        $csvContent = "date,category,description,amount,type\n";
        $csvContent .= "2024-02-01,Food,Lunch,15.50,expense\n";
        $csvContent .= "2024-02-02,Salary,Monthly,3000.00,income\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions/import-preview', [
                'file' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.data.category_name', 'Food')
            ->assertJsonPath('data.1.data.type', 'income');
            
        // Verify category was auto-created or found
        $this->assertDatabaseHas('categories', ['name' => 'Food', 'user_id' => $this->user->id]);
    }

    public function test_detects_duplicates_during_preview()
    {
        $category = Category::factory()->create(['name' => 'Groceries', 'user_id' => $this->user->id, 'type' => 'expense']);
        
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'date' => '2024-02-05',
            'amount' => 50.00,
            'type' => 'expense',
            'description' => 'Weekly shop',
        ]);

        $csvContent = "date,category,description,amount,type\n";
        $csvContent .= "2024-02-05,Groceries,Weekly shop,50.00,expense\n"; // Duplicate
        $csvContent .= "2024-02-06,Groceries,New shop,30.00,expense\n";    // New

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions/import-preview', [
                'file' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.0.is_duplicate', true)
            ->assertJsonPath('data.1.is_duplicate', false);
    }

    public function test_can_confirm_import()
    {
        $category = Category::factory()->create(['name' => 'Rent', 'user_id' => $this->user->id, 'type' => 'expense']);
        
        $items = [
            [
                'skip' => false,
                'data' => [
                    'date' => '2024-02-01',
                    'category_id' => $category->id,
                    'description' => 'Feb Rent',
                    'amount' => 1200.00,
                    'type' => 'expense'
                ]
            ],
            [
                'skip' => true,
                'data' => [
                    'date' => '2024-01-01',
                    'category_id' => $category->id,
                    'description' => 'Jan Rent',
                    'amount' => 1200.00,
                    'type' => 'expense'
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions/import-confirm', [
                'items' => $items,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.skipped', 1);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Feb Rent',
            'user_id' => $this->user->id
        ]);
        
        $this->assertDatabaseMissing('transactions', [
            'description' => 'Jan Rent',
            'user_id' => $this->user->id
        ]);
    }
}
