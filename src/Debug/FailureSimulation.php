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

    /**
     * Factory: build simulation config from persisted options.
     */
    public static function fromOptions(): self
    {
        $enabled = (bool) get_option(
            FailureSimulationOptions::ENABLED,
            false
        );

        if (!$enabled) {
            return new self(false, null);
        }

        $rawCategory = get_option(
            FailureSimulationOptions::CATEGORY,
            null
        );

        $category = FailureCategory::tryFrom((string) $rawCategory);

        return new self(
            true,
            $category ?? FailureCategory::UNKNOWN
        );
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
