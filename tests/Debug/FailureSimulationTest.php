<?php

declare(strict_types=1);

namespace WCSPO\Tests\Debug;

use PHPUnit\Framework\TestCase;
use WCSPO\Debug\FailureSimulation;
use WCSPO\Domain\FailureCategory;

final class FailureSimulationTest extends TestCase
{
    public function test_disabled_simulation_returns_null(): void
    {
        $sim = new FailureSimulation(false, FailureCategory::RETRYABLE_TRANSIENT);

        $this->assertNull($sim->simulate());
    }

    public function test_enabled_simulation_returns_failure(): void
    {
        $sim = new FailureSimulation(true, FailureCategory::RETRYABLE_TRANSIENT);

        $this->assertSame(
            FailureCategory::RETRYABLE_TRANSIENT,
            $sim->simulate()
        );
    }
}
