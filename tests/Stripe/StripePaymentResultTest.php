<?php

namespace WCSPO\Tests\Stripe;

use PHPUnit\Framework\TestCase;
use WCSPO\Stripe\StripePaymentResult;

final class StripePaymentResultTest extends TestCase
{
    public function test_successful_payment_intent(): void
    {
        $pi = [
            'status' => 'succeeded',
        ];

        $result = StripePaymentResult::fromPaymentIntent($pi);

        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertNull($result->getFailureCode());
        $this->assertNull($result->getFailureType());
    }

    public function test_insufficient_funds_is_not_retryable(): void
    {
        $pi = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'insufficient_funds',
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($pi);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isRetryable());
        $this->assertSame('insufficient_funds', $result->getFailureCode());
        $this->assertSame('card_error', $result->getFailureType());
    }

    public function test_api_error_is_retryable(): void
    {
        $pi = [
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'api_error',
                'code' => 'internal_error',
            ],
        ];

        $result = StripePaymentResult::fromPaymentIntent($pi);

        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isRetryable());
    }
}