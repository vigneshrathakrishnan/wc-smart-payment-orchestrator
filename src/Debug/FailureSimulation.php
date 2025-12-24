<?php

declare(strict_types=1);

namespace WCSPO\Debug;

use WCSPO\Contracts\FailureSimulationInterface;
use WCSPO\Domain\FailureCategory;

final class FailureSimulation implements FailureSimulationInterface
{
    private bool $enabled;
    private ?FailureCategory $simulatedFailure;

    public function __construct(
        bool $enabled,
        ?FailureCategory $simulatedFailure
    ) {
        $this->enabled = $enabled;
        $this->simulatedFailure = $simulatedFailure;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function simulate(): ?FailureCategory
    {
        if (!$this->enabled) {
            return null;
        }

        return $this->simulatedFailure;
    }
}
