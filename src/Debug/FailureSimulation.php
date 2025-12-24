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
    public static function fromOptions(array $options): self
    {
        $enabled = (bool) ($options['enabled'] ?? false);

        if (!$enabled) {
            return new self(false, null);
        }

        $rawCategory = $options['category'] ?? null;

        $category = FailureCategory::tryFrom((string) $rawCategory);

        return new self(true, $category);
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
