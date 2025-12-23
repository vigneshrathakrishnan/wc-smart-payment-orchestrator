<?php

namespace WCSPO;

use WCSPO\Orchestrator\StripeOrchestrator;

final class Bootstrap
{
    public static function init(): void
    {
        $logger = wc_get_logger();

        // Register orchestration hooks
        StripeOrchestrator::register();

        // Register payment lifecycle listener
        $listener = new \WCSPO\WooCommerce\PaymentLifecycleListener($logger, $stripeClient);
        $listener->register();

        // $logger->debug(
        //     \WCSPO\Debug\Ping::check(),
        //     [ 'source' => 'wc-smart-payment-orchestrator' ]
        // );

    }
}