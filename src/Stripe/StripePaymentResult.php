<?php

namespace WCSPO\Stripe;

use WCSPO\Contracts\PaymentResultInterface;

/**
 * Value object representing the outcome of a Stripe payment attempt.
 *
 * Built from real Stripe API responses (PaymentIntent / Charge).
 * No side effects. No WooCommerce coupling.
 */
final class StripePaymentResult implements PaymentResultInterface
{
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
        $this->status       = $status;
        $this->failureCode  = $failureCode;
        $this->failureType  = $failureType;
        $this->retryable    = $retryable;
    }

    /**
     * Factory from Stripe PaymentIntent response.
     */
    public static function fromPaymentIntent(array $pi): self
    {
        $status = $pi['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            return new self('succeeded', null, null, false);
        }

        $error = $pi['last_payment_error'] ?? null;

        if (!$error) {
            return new self($status, null, null, false);
        }

        $failureCode = $error['code'] ?? null;
        $failureType = $error['type'] ?? null;

        return new self(
            $status,
            $failureCode,
            $failureType,
            self::isRetryableFailure($error)
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
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
     * Retry rules derived strictly from Stripe semantics
     * and validated against real logs.
     */
    private static function isRetryableFailure(array $error): bool
    {
        $type = $error['type'] ?? null;
        $code = $error['code'] ?? null;

        // Retry-safe Stripe failures
        if (in_array($type, ['api_error', 'rate_limit_error'], true)) {
            return true;
        }

        // Network / timeout class errors
        if ($code === 'lock_timeout') {
            return true;
        }

        // Explicitly NOT retryable (from logs)
        $nonRetryableCodes = [
            'card_declined',
            'insufficient_funds',
            'fraudulent',
            'authentication_required',
            'generic_decline',
        ];

        if (in_array($code, $nonRetryableCodes, true)) {
            return false;
        }

        return false;
    }
}
