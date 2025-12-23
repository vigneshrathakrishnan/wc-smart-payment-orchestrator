<?php

declare(strict_types=1);

namespace WCSPO\WooCommerce;

use WC_Order;
use WC_Logger;

final class PaymentLifecycleListener
{
    private WC_Logger $logger;

    public function __construct(WC_Logger $logger)
    {
        $this->logger = $logger;
    }

    public function register(): void
    {
        add_action(
            'woocommerce_payment_complete',
            [$this, 'onPaymentComplete'],
            10,
            1
        );

        add_action(
            'woocommerce_order_status_failed',
            [$this, 'onPaymentFailed'],
            10,
            1
        );
    }

    private function extractStripeContext(WC_Order $order): ?array
    {
        $intentId = $order->get_meta('_stripe_intent_id');

        if (!$intentId) {
            return null;
        }

        return [
            'payment_intent_id' => $intentId,
            'status'            => $order->get_status(),
            'order_id'          => $order->get_id(),
        ];
    }


    public function onPaymentComplete(int $orderId): void
    {
        $order = wc_get_order($orderId);

        if (!$order instanceof WC_Order) {
            return;
        }

        $context = $this->extractStripeContext($order);

        if ($context === null) {
            $this->logger->warning(
                'WCSPO: Stripe metadata missing on payment complete',
                ['order_id' => $orderId]
            );
            return;
        }

        $this->logger->info(
            'WCSPO: Stripe payment completed',
            $context
        );
    }

    public function onPaymentFailed(int $orderId): void
    {
       $order = wc_get_order($orderId);

        if (!$order instanceof WC_Order) {
            return;
        }

        $context = $this->extractStripeContext($order);

        if ($context === null) {
            $this->logger->warning(
                'WCSPO: Stripe metadata missing on payment failure',
                ['order_id' => $orderId]
            );
            return;
        }

        $this->logger->info(
            'WCSPO: Stripe payment failure detected',
            $context
        );
    }
}
