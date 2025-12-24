<?php

declare(strict_types=1);

namespace WCSPO\Orchestrator;

use WCSPO\Stripe\StripePaymentResult;
use WCSPO\Stripe\StripeFailureMapper;
use WCSPO\Domain\RetryDecision;
use WCSPO\Contracts\RetryPolicyInterface;
use WCSPO\Domain\FailureCategory;
use WCSPO\Contracts\FailureSimulationInterface;

final class StripePaymentOrchestrator
{
    private ?FailureSimulationInterface $simulation;

    public function __construct(
        private RetryPolicyInterface $retryPolicy,
        ?FailureSimulationInterface $simulation = null
    ) {
        $this->simulation = $simulation;
    }

    public function orchestrate(array $paymentIntent, int $attempt): PaymentOrchestrationResult
    {
        // Stripe truth (observational, not decision-driving here)
        $stripeResult = StripePaymentResult::fromPaymentIntent($paymentIntent);

        // Success short-circuit
        if ($stripeResult->isSuccessful()) {
            return new PaymentOrchestrationResult(
                true,
                false,
                FailureCategory::UNKNOWN
            );
        }

        // Failure classification (simulation overrides Stripe)
        $failureCategory = $this->simulation && $this->simulation->isEnabled()
            ? ($this->simulation->simulate() ?? FailureCategory::UNKNOWN)
            : StripeFailureMapper::classify($paymentIntent);

        // Retry decision (pure domain logic)
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
