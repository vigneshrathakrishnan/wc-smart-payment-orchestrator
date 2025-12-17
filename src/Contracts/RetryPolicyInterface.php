<?php

namespace WCSPO\Contracts;

interface RetryPolicyInterface
{
    /**
     * Determines whether a payment attempt should be retried
     * based on the classified payment result.
     */
    public function shouldRetry(PaymentResultInterface $result): bool;
}
