<?php
/**
 * Plugin Name: WC Smart Payment Orchestrator
 * Description: Stripe-first payment orchestration for WooCommerce focused on reliability and observability.
 * Version: 0.1.0
 * Author: Vignesh Kumar Radhakrishnan
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

// 1. Autoload (Composer later)
require_once __DIR__ . '/vendor/autoload.php';

// 2. Boot only when WooCommerce is active
add_action('plugins_loaded', function () {
    if (! class_exists('WooCommerce')) {
        return;
    }

    \WCSPO\Bootstrap::init();
});
