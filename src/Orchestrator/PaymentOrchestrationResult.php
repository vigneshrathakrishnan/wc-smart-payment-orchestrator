<?php

declare(strict_types=1);

namespace WCSPO\Orchestrator;

use WCSPO\Domain\FailureCategory;

final class PaymentOrchestrationResult
{
    public function __construct(
        private bool $successful,
        private bool $retryable,
        private FailureCategory $failureCategory
    ) {}

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function shouldRetry(): bool
    {
        return $this->retryable;
    }

    public function failureCategory(): FailureCategory
    {
        return $this->failureCategory;
    }
}
