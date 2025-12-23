<?php

declare(strict_types=1);

namespace WCSPO\Tests\Stripe;

use PHPUnit\Framework\TestCase;
use WCSPO\Stripe\StripeFailureMapper;
use WCSPO\Domain\FailureCategory;

final class StripeFailureMapperTest extends TestCase
{
    public function test_requires_action_is_customer_action_required(): void
    {
        $payload = [
            'status' => 'requires_action',
        ];

        $this->assertSame(
            FailureCategory::CUSTOMER_ACTION_REQUIRED,
            StripeFailureMapper::classify($payload)
        );
    }

    public function test_card_error_is_hard_decline(): void
    {
        $payload = [
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'card_declined',
                'decline_code' => 'insufficient_funds',
            ],
        ];

        $this->assertSame(
            FailureCategory::HARD_DECLINE,
            StripeFailureMapper::classify($payload)
        );
    }

    public function test_try_again_later_is_retryable(): void
    {
        $payload = [
            'last_payment_error' => [
                'advice_code' => 'try_again_later',
                'type' => 'card_error',
            ],
        ];

        $this->assertSame(
            FailureCategory::RETRYABLE_TRANSIENT,
            StripeFailureMapper::classify($payload)
        );
    }

    public function test_api_error_is_retryable(): void
    {
        $payload = [
            'last_payment_error' => [
                'type' => 'api_error',
            ],
        ];

        $this->assertSame(
            FailureCategory::RETRYABLE_TRANSIENT,
            StripeFailureMapper::classify($payload)
        );
    }

    public function test_unknown_payload_is_unknown(): void
    {
        $payload = [];

        $this->assertSame(
            FailureCategory::UNKNOWN,
            StripeFailureMapper::classify($payload)
        );
    }
}
