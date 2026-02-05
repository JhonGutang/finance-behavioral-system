<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\CategoryRepository;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CsvImportService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private CategoryRepository $categoryRepository
    ) {}

    /**
     * Parse CSV and return a preview of data with duplicate flags.
     */
    public function getPreview(UploadedFile $file, int $userId): array
    {
        $rows = $this->parseCsv($file);
        $preview = [];

        foreach ($rows as $index => $row) {
            try {
                $validated = $this->validateRow($row);
                
                // Find or create category if name is provided
                $category = $this->findOrCreateCategory($validated['category'], $validated['type'], $userId);
                
                $data = [
                    'date' => $validated['date'],
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'description' => $validated['description'],
                    'amount' => (float) $validated['amount'],
                    'type' => $validated['type'],
                ];

                $isDuplicate = $this->checkIsDuplicate($data, $userId);

                $preview[] = [
                    'id' => $index, // Temporary ID for frontend
                    'data' => $data,
                    'is_duplicate' => $isDuplicate,
                    'status' => 'valid',
                ];
            } catch (\Exception $e) {
                $preview[] = [
                    'id' => $index,
                    'raw_data' => $row,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $preview;
    }

    /**
     * Process the confirmed import.
     */
    public function confirmImport(array $items, int $userId): array
    {
        $importedCount = 0;
        $skippedCount = 0;

        foreach ($items as $item) {
            if ($item['skip'] ?? false) {
                $skippedCount++;
                continue;
            }

            $data = $item['data'];
            $data['user_id'] = $userId;

            $this->transactionRepository->create($data);
            $importedCount++;
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
        ];
    }

    private function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle); // First row as headers
        
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($headers) !== count($data)) continue;
            $rows[] = array_combine($headers, $data);
        }
        fclose($handle);

        return $rows;
    }

    private function validateRow(array $row): array
    {
        $rules = [
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
        ];

        $validator = Validator::make($row, $rules);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        return $validator->validated();
    }

    private function findOrCreateCategory(string $name, string $type, int $userId): Category
    {
        // Try to find existing category (case insensitive-ish depends on DB)
        $category = Category::where('name', $name)
            ->where('type', $type)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->first();

        if (!$category) {
            // Create a new user-specific category
            $category = $this->categoryRepository->create([
                'name' => $name,
                'type' => $type,
                'user_id' => $userId,
                'color' => '#64748b', // Default slate color
                'icon' => 'Tag', // Default icon
                'is_default' => false,
            ]);
        }

        return $category;
    }

    private function checkIsDuplicate(array $data, int $userId): bool
    {
        return Transaction::where('user_id', $userId)
            ->where('date', $data['date'])
            ->where('amount', $data['amount'])
            ->where('type', $data['type'])
            ->where('description', $data['description'])
            ->exists();
    }
}
