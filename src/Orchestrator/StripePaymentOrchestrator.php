<?php

declare(strict_types=1);

namespace WCSPO\Orchestrator;

use WCSPO\Stripe\StripePaymentResult;
use WCSPO\Stripe\StripeFailureMapper;
use WCSPO\Domain\RetryDecision;
use WCSPO\Contracts\RetryPolicyInterface;
use WCSPO\Domain\FailureCategory;

final class StripePaymentOrchestrator
{
    public function __construct(
        private RetryPolicyInterface $retryPolicy
    ) {}

    public function orchestrate(array $paymentIntent, int $attempt): PaymentOrchestrationResult
    {
        // Stripe truth
        $stripeResult = StripePaymentResult::fromPaymentIntent($paymentIntent);

        // Success short-circuit
        if ($stripeResult->isSuccessful()) {
            return new PaymentOrchestrationResult(
                true,
                false,
                FailureCategory::UNKNOWN
            );
        }

        // Domain failure classification
        $failureCategory = StripeFailureMapper::classify($paymentIntent);

        // Retry decision (pure logic)
        $shouldRetry = RetryDecision::shouldRetry(
            $failureCategory,
            $this->retryPolicy,
            $attempt
        );

        return new PaymentOrchestrationResult(
            false,
            $shouldRetry,
            $failureCategory
        );
    }
}
