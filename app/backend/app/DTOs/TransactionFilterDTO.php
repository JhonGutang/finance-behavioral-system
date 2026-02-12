<?php

namespace App\DTOs;

class TransactionFilterDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly array $filters,
        public readonly int $perPage = 10
    ) {}

    public static function from(int $userId, array $filters, int $perPage = 10): self
    {
        return new self($userId, $filters, $perPage);
    }
}
