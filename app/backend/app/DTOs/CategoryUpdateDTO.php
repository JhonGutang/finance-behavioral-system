<?php

namespace App\DTOs;

class CategoryUpdateDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly array $data
    ) {}

    public static function from(int $id, int $userId, array $data): self
    {
        return new self($id, $userId, $data);
    }
}
