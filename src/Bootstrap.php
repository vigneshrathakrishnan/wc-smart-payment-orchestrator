<?php

namespace WCSPO;

use WCSPO\Orchestrator\StripeOrchestrator;

final class Bootstrap
{
    public static function init(): void
    {
        // Register orchestration hooks
        StripeOrchestrator::register();
    }
}