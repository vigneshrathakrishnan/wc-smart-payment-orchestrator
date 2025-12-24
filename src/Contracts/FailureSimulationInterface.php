<?php

declare(strict_types=1);

namespace WCSPO\Contracts;

use WCSPO\Domain\FailureCategory;

interface FailureSimulationInterface
{
    /**
     * Whether simulation is currently enabled.
     */
    public function isEnabled(): bool;

    /**
     * Returns a simulated failure category
     * matching real Stripe behavior.
     *
     * Null means "no simulation".
     */
    public function simulate(): ?FailureCategory;
}
