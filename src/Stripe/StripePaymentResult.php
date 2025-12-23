<?php

declare(strict_types=1);

namespace WCSPO\Stripe;

use WCSPO\Contracts\PaymentResultInterface;

/**
 * Immutable value object representing the outcome of a Stripe payment attempt.
 *
 * - Built from real Stripe PaymentIntent responses
 * - No WooCommerce coupling
 * - No orchestration logic
 * - Stripe-semantics only
 */
final class StripePaymentResult implements PaymentResultInterface
{
    private const STATUS_SUCCEEDED = 'succeeded';
    private const STATUS_REQUIRES_ACTION = 'requires_action';

    private string $status;
    private ?string $failureCode;
    private ?string $failureType;
    private bool $retryable;

    private function __construct(
        string $status,
        ?string $failureCode,
        ?string $failureType,
        bool $retryable
    ) {
        $this->status      = $status;
        $this->failureCode = $failureCode;
        $this->failureType = $failureType;
        $this->retryable   = $retryable;
    }

    /**
     * Factory from Stripe PaymentIntent payload.
     */
    public static function fromPaymentIntent(array $pi): self
    {
        $status = $pi['status'] ?? 'unknown';

        // Fully successful payment
        if ($status === self::STATUS_SUCCEEDED) {
            return new self(self::STATUS_SUCCEEDED, null, null, false);
        }

        // Customer action required (3DS, SCA, etc.)
        if ($status === self::STATUS_REQUIRES_ACTION) {
            return new self(
                self::STATUS_REQUIRES_ACTION,
                null,
                'customer_action_required',
                false
            );
        }

        // Failed with Stripe error payload
        $error = $pi['last_payment_error'] ?? null;

        if (!$error || !is_array($error)) {
            return new self($status, null, null, false);
        }

        $failureCode = $error['code'] ?? null;
        $failureType = $error['type'] ?? null;

        return new self(
            $status,
            $failureCode,
            $failureType,
            self::isRetryableStripeError($error)
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function getFailureCode(): ?string
    {
        return $this->failureCode;
    }

    public function getFailureType(): ?string
    {
        return $this->failureType;
    }

    /**
     * Stripe-level retry safety rules.
     * These are NOT business rules — only Stripe semantics.
     */
    private static function isRetryableStripeError(array $error): bool
    {
        $type = $error['type'] ?? null;
        $code = $error['code'] ?? null;

        // Retry-safe transient failures (from real logs)
        if (in_array($type, ['api_error', 'rate_limit_error'], true)) {
            return true;
        }

        // Network / timeout edge cases
        if ($code === 'lock_timeout') {
            return true;
        }

        // Explicit non-retryable failures
        return false;
    }
}
