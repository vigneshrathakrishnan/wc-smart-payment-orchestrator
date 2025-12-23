<?php

declare(strict_types=1);

namespace WCSPO\Tests\Domain;

use PHPUnit\Framework\TestCase;
use WCSPO\Domain\RetryDecision;
use WCSPO\Domain\FailureCategory;
use WCSPO\Contracts\RetryPolicyInterface;
use WCSPO\Contracts\PaymentResultInterface;

final class RetryDecisionTest extends TestCase
{
    private function policy(int $maxAttempts, bool $allowed = true): RetryPolicyInterface
    {
        return new class ($maxAttempts, $allowed) implements RetryPolicyInterface {
            public function __construct(
                private int $max,
                private bool $allowed
            ) {}

            public function shouldRetry(PaymentResultInterface $result): bool
            {
                return true; // value irrelevant for RetryDecision
            }

            public function maxAttempts(): int
            {
                return $this->max;
            }

            public function allowsRetries(): bool
            {
                return $this->allowed;
            }
        };
    }

    public function test_retryable_failure_allows_retry(): void
    {
        $this->assertTrue(
            RetryDecision::shouldRetry(
                FailureCategory::RETRYABLE_TRANSIENT,
                $this->policy(1),
                0
            )
        );
    }

    public function test_hard_decline_never_retries(): void
    {
        $this->assertFalse(
            RetryDecision::shouldRetry(
                FailureCategory::HARD_DECLINE,
                $this->policy(3),
                0
            )
        );
    }

    public function test_exceeding_max_attempts_blocks_retry(): void
    {
        $this->assertFalse(
            RetryDecision::shouldRetry(
                FailureCategory::RETRYABLE_TRANSIENT,
                $this->policy(1),
                1
            )
        );
    }

    public function test_policy_can_disable_retries(): void
    {
        $this->assertFalse(
            RetryDecision::shouldRetry(
                FailureCategory::RETRYABLE_TRANSIENT,
                $this->policy(3, false),
                0
            )
        );
    }
}
