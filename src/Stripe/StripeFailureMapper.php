<?php

namespace WCSPO\Stripe;

use WCSPO\Domain\FailureCategory;

final class StripeFailureMapper
{
    /**
     * @param array<string, mixed> $stripePayload
     */
    public static function classify(array $stripePayload): FailureCategory
    {
        // Explicit requires_action (3DS, auth flows)
        if (
            isset($stripePayload['status']) &&
            $stripePayload['status'] === 'requires_action'
        ) {
            return FailureCategory::CUSTOMER_ACTION_REQUIRED;
        }

        // last_payment_error (PaymentIntent-based failures)
        if (isset($stripePayload['last_payment_error'])) {
            $lpe = $stripePayload['last_payment_error'];

            // Retryable Stripe errors
            if (
                in_array($lpe['type'] ?? null, [
                    'api_error',
                    'rate_limit_error',
                ], true)
            ) {
                return FailureCategory::RETRYABLE_TRANSIENT;
            }

            // Advice-based retry hint
            if (($lpe['advice_code'] ?? null) === 'try_again_later') {
                return FailureCategory::RETRYABLE_TRANSIENT;
            }

            // Card / issuer failures
            if (($lpe['type'] ?? null) === 'card_error') {
                return FailureCategory::HARD_DECLINE;
            }

            return FailureCategory::UNKNOWN;
        }

        return FailureCategory::UNKNOWN;
    }
}
