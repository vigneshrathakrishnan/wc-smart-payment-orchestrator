<?php

declare(strict_types=1);

namespace WCSPO\Tests\Orchestrator;

use PHPUnit\Framework\TestCase;
use WCSPO\Orchestrator\StripePaymentOrchestrator;
use WCSPO\Contracts\RetryPolicyInterface;
use WCSPO\Contracts\PaymentResultInterface;
use WCSPO\Domain\FailureCategory;

final class StripePaymentOrchestratorTest extends TestCase
{
    private function retryPolicy(int $maxAttempts, bool $allowed = true): RetryPolicyInterface
    {
        return new class ($maxAttempts, $allowed) implements RetryPolicyInterface {
            public function __construct(
                private int $max,
                private bool $allowed
            ) {}

            public function shouldRetry(PaymentResultInterface $result): bool
            {
                return true; // decision happens in RetryDecision, not here
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

    public function test_successful_payment_never_retries(): void
    {
        $orchestrator = new StripePaymentOrchestrator($this->retryPolicy(3));

        $payload = [
            'status' => 'succeeded',
        ];

        $result = $orchestrator->orchestrate($payload, 0);

        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->shouldRetry());
    }

    public function test_insufficient_funds_never_retries(): void
    {
        $orchestrator = new StripePaymentOrchestrator($this->retryPolicy(3));

        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'insufficient_funds',
            ],
        ];

        $result = $orchestrator->orchestrate($payload, 0);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->shouldRetry());
        $this->assertSame(FailureCategory::HARD_DECLINE, $result->failureCategory());
    }

    public function test_api_error_allows_retry_when_policy_allows(): void
    {
        $orchestrator = new StripePaymentOrchestrator($this->retryPolicy(2));

        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'api_error',
            ],
        ];

        $result = $orchestrator->orchestrate($payload, 0);

        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->shouldRetry());
        $this->assertSame(FailureCategory::RETRYABLE_TRANSIENT, $result->failureCategory());
    }

    public function test_retry_blocked_when_policy_disallows(): void
    {
        $orchestrator = new StripePaymentOrchestrator($this->retryPolicy(3, false));

        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'api_error',
            ],
        ];

        $result = $orchestrator->orchestrate($payload, 0);

        $this->assertFalse($result->shouldRetry());
    }

    public function test_retry_blocked_when_max_attempts_exceeded(): void
    {
        $orchestrator = new StripePaymentOrchestrator($this->retryPolicy(1));

        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'api_error',
            ],
        ];

        $result = $orchestrator->orchestrate($payload, 1);

        $this->assertFalse($result->shouldRetry());
    }
}
