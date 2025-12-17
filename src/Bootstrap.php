<?php

namespace WCSPO;

use WCSPO\Orchestrator\StripeOrchestrator;

final class Bootstrap
{
    public static function init(): void
    {
        // Register orchestration hooks
        StripeOrchestrator::register();

        // $logger = wc_get_logger();

        // $logger->debug(
        //     \WCSPO\Debug\Ping::check(),
        //     [ 'source' => 'wc-smart-payment-orchestrator' ]
        // );

    }
}