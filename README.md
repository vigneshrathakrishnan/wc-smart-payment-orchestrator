# WC Smart Payment Orchestrator (MVP)

A Stripe-first payment orchestration plugin for WooCommerce focused on **reliability, observability, and correctness** of card payments.

This project is intentionally scoped as an **engineering MVP**, not a feature-heavy gateway replacement.

---

## 🎯 Problem Statement

WooCommerce merchants rely on payment gateways like Stripe for high-volume transactions.  
However, real-world payment flows exhibit:

- Transient API failures (network, rate limits)
- Ambiguous payment states (requires_action, retryable failures)
- Limited visibility into payment lifecycle behavior
- Tight coupling between WooCommerce order state and gateway responses

This plugin explores **how to improve payment robustness and insight without changing the customer checkout experience**.

---

## 🧠 MVP Philosophy

- **No UI disruption**
- **No gateway replacement**
- **No SaaS backend**
- **No multi-gateway routing**

This plugin works **alongside the official WooCommerce Stripe plugin** and focuses on orchestration concerns **after checkout submission**.

---

## ✅ MVP Scope (Locked)

### 1. Supported Gateway
- Stripe (WooCommerce Stripe Gateway)
- Payment method: **Card**  
  (Apple Pay & Google Pay implicitly covered as Stripe card flows)

### 2. Retry Orchestration
Retries are applied **only** for *transient, retry-safe failures*:

- `api_error`
- `rate_limit_error`
- network / timeout errors

**Explicitly excluded from retry:**
- Insufficient funds
- Card declined
- Fraud / Radar blocks
- Authentication failures

Retry characteristics:
- Max: **1 retry**
- Time-bounded (few seconds)
- Fully transparent to customer

---

### 3. Payment Health Tracking
Lightweight, in-plugin health signals derived from Stripe responses:

- Success vs failure ratio
- Failure type classification
- Retry attempts & outcomes
- Stripe availability indicators (soft signals)

No external monitoring system required.

---

### 4. Payment Analytics (Local Only)
Collected per order / attempt:

- Payment intent lifecycle
- Failure category
- Retry applied (yes/no)
- Final resolution status

Stored using WordPress / WooCommerce primitives only.

---

### 5. Reconciliation Awareness (Not Re-implementation)
This plugin **does not replace WooCommerce reconciliation**.

Instead, it:
- Observes Stripe → WooCommerce state transitions
- Logs webhook vs API response mismatches
- Highlights edge cases for future enhancement

---

## 🚫 Out of Scope (Intentional)

- Multiple gateways (Razorpay, PayPal, etc.)
- Gateway UI selection
- Customer-facing payment method changes
- Automated order state correction
- Refunds, disputes, payouts
- SaaS dashboards

---

## 🧩 Architecture Overview

- **Adapter pattern** over Stripe gateway behavior
- Strict separation of:
  - Orchestration logic
  - Gateway interaction
  - Observability & logging
- SOLID-compliant class design
- Composer autoloading
- Testable units (PHPUnit)

---

## 🛠️ Engineering Standards

- PHP 8+
- WordPress Coding Standards (PHPCS)
- PHPUnit for unit tests
- Docker-based local environment
- Git with PR-based workflow (even solo)
- CI pipeline for lint + test

---

## 🧪 Testing Strategy

- Stripe test mode only
- Controlled simulation of:
  - Successful payments
  - Insufficient funds
  - Card declined
  - Requires_action (3DS)
  - Retry-eligible failures (mocked)

---

## 📦 Installation (Dev)

```bash
wp-content/plugins/
└── wc-smart-payment-orchestrator/
