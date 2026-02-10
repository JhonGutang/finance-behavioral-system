<?php

namespace App\DTOs;

class FeedbackTemplateInputDTO
{
    public function __construct(
        public readonly array $template,
        public readonly array $data,
        public readonly int $userId,
        public readonly string $ruleId,
        public readonly string $level
    ) {}

    public static function from(array $template, array $data, int $userId, string $ruleId, string $level): self
    {
        return new self(
            template: $template,
            data: $data,
            userId: $userId,
            ruleId: $ruleId,
            level: $level
        );
    }
}
