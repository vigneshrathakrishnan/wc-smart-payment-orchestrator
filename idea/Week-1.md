### Short answer first

**No — do NOT use Xdebug for this week.**

---

### Why Xdebug is the wrong tool *right now*

* It **slows PHP execution significantly**
* Stripe + WooCommerce flows are **event-driven**, not step-through friendly
* Most failures you care about are:

  * API responses
  * Webhook payloads
  * State transitions
* These are better understood via **logs + assertions**, not breakpoints

Xdebug is useful for algorithm-heavy code.
This project is **systems + integration heavy**.

---

### What to use instead (senior-grade setup)

* **Structured logging** (Monolog or WC logger)
* **Order notes + metadata**
* **Request/response dumps in test mode**
* **PHPUnit tests** for core logic

This mirrors how payments are debugged in real companies.

---

### When Xdebug *does* make sense (later, optional)

* If you’re debugging:

  * A weird WooCommerce hook order
  * A fatal error during bootstrap
* Use it **temporarily**, then turn it off

---

### Phase split for THIS WEEK (15 hours)

#### Day 1 (Mon – 3h)

* Install Stripe plugin (test mode)
* Complete success + failure payments
* Log lifecycle events

#### Day 2 (Tue – 3h)

* Scaffold orchestrator plugin
* Register WC gateway
* Log `process_payment` flow

#### Day 3 (Wed – 3h)

* Stripe adapter skeleton
* Capture error responses
* Build failure classifier

#### Day 4 (Thu – 3h)

* Implement retry logic
* Simulate `api_error` and `rate_limit_error`
* Persist attempts

#### Day 5 (Fri – 3h)

* Health tracking
* Basic analytics table
* Cleanup + docs

---

### Final rule (important)

> **If you need Xdebug constantly, your architecture is unclear.
> If logs + tests are enough, your design is solid.**

Skip Xdebug for now.
You’re making the correct engineering tradeoff.
