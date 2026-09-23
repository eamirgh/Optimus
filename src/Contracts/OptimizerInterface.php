<?php

namespace Eamirgh\Optimus\Contracts;

interface OptimizerInterface
{
    /**
     * Secondary lossy/lossless binary optimization.
     */
    public function optimize(string $binary, string $format): string;

    /**
     * Check if the optimizer is installed and available.
     */
    public function isAvailable(): bool;
}
