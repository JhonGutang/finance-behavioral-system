<?php

namespace App\DTOs;

class DateRangeQueryDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $startDate,
        public readonly string $endDate
    ) {}

    public static function from(int $userId, string $startDate, string $endDate): self
    {
        return new self($userId, $startDate, $endDate);
    }
}
