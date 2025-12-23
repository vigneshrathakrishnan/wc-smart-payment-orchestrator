<?php

declare(strict_types=1);

namespace WCSPO\Tests\Stripe;

use PHPUnit\Framework\TestCase;
use WCSPO\Stripe\StripePaymentResult;

final class StripePaymentResultTest extends TestCase
{
    public function test_succeeded_payment_is_successful(): void
    {
        $payload = [
            'status' => 'succeeded',
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertNull($result->getFailureCode());
        $this->assertNull($result->getFailureType());
    }

    public function test_requires_action_is_not_retryable(): void
    {
        $payload = [
            'status' => 'requires_action',
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertSame('customer_action_required', $result->getFailureType());
    }

    public function test_insufficient_funds_is_not_retryable(): void
    {
        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'insufficient_funds',
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertSame('insufficient_funds', $result->getFailureCode());
        $this->assertSame('card_error', $result->getFailureType());
    }

    public function test_card_declined_is_not_retryable(): void
    {
        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'card_declined',
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertFalse($result->isRetryable());
        $this->assertSame('card_declined', $result->getFailureCode());
    }

    public function test_api_error_is_retryable(): void
    {
        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'api_error',
                'code' => null,
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isRetryable());
        $this->assertSame('api_error', $result->getFailureType());
    }

    public function test_rate_limit_error_is_retryable(): void
    {
        $payload = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'rate_limit_error',
                'code' => null,
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertTrue($result->isRetryable());
    }

    public function test_missing_error_payload_is_safe(): void
    {
        $payload = [
            'status' => 'requires_payment_method',
        ];

        $result = StripePaymentResult::fromPaymentIntent($payload);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertNull($result->getFailureCode());
        $this->assertNull($result->getFailureType());
    }
}
