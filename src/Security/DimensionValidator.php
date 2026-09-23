<?php

namespace Eamirgh\Optimus\Security;

use Eamirgh\Optimus\Exceptions\InvalidDimensionException;

class DimensionValidator
{
    /**
     * @param array<int, array{0: int|null, 1: int|null}> $allowedDimensions
     */
    public function __construct(
        protected array $allowedDimensions = [],
        protected int $maxWidth = 3840,
        protected int $maxHeight = 2160
    ) {}

    /**
     * Check if the requested dimensions are permitted.
     */
    public function isValid(?int $width, ?int $height): bool
    {
        // Must specify at least one dimension or both null (original)
        if ($width === null && $height === null) {
            return true;
        }

        if ($width !== null && ($width <= 0 || $width > $this->maxWidth)) {
            return false;
        }

        if ($height !== null && ($height <= 0 || $height > $this->maxHeight)) {
            return false;
        }

        // If whitelist is empty, only bound checks apply
        if (empty($this->allowedDimensions)) {
            return true;
        }

        foreach ($this->allowedDimensions as $pair) {
            $allowedW = $pair[0] ?? null;
            $allowedH = $pair[1] ?? null;

            $wMatch = ($allowedW === null) || ($allowedW === $width);
            $hMatch = ($allowedH === null) || ($allowedH === $height);

            if ($wMatch && $hMatch) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate dimensions, throwing exception if invalid.
     *
     * @throws InvalidDimensionException
     */
    public function validate(?int $width, ?int $height): void
    {
        if ($width !== null && $width > $this->maxWidth) {
            throw InvalidDimensionException::exceedsMaximum($width, $height, $this->maxWidth, $this->maxHeight);
        }

        if ($height !== null && $height > $this->maxHeight) {
            throw InvalidDimensionException::exceedsMaximum($width, $height, $this->maxWidth, $this->maxHeight);
        }

        if (! $this->isValid($width, $height)) {
            throw InvalidDimensionException::notAllowed($width, $height);
        }
    }

    public function getAllowedDimensions(): array
    {
        return $this->allowedDimensions;
    }
}
