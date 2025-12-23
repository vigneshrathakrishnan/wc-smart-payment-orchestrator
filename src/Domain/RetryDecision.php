<?php

declare(strict_types=1);

namespace WCSPO\Domain;

use WCSPO\Contracts\RetryPolicyInterface;

final class RetryDecision
{
    public static function shouldRetry(
        FailureCategory $failureCategory,
        RetryPolicyInterface $policy,
        int $attempt
    ): bool {
        if (! $policy->allowsRetries()) {
            return false;
        }

        if ($attempt >= $policy->maxAttempts()) {
            return false;
        }

        return match ($failureCategory) {
            FailureCategory::RETRYABLE_TRANSIENT => true,
            default => false,
        };
    }
}
