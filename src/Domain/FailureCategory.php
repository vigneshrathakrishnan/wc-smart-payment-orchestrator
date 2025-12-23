<?php

declare(strict_types=1);

namespace WCSPO\Domain;

enum FailureCategory: string
{
    case RETRYABLE_TRANSIENT = 'retryable_transient';
    case HARD_DECLINE = 'hard_decline';
    case CUSTOMER_ACTION_REQUIRED = 'customer_action_required';
    case UNKNOWN = 'unknown';
}