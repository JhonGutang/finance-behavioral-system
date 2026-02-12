<?php

namespace App\DTOs;

class CategoryLookupDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly int $userId
    ) {}

    public static function from(string $name, string $type, int $userId): self
    {
        return new self($name, $type, $userId);
    }
}
