<?php

namespace WCSPO\Contracts;

interface PaymentResultInterface
{
    public function isSuccessful(): bool;

    public function isRetryable(): bool;

    public function getFailureCode(): ?string;

    public function getFailureType(): ?string;
}
