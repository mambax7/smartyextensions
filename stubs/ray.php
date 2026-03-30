<?php

/**
 * PHPStan stubs for spatie/ray.
 */

namespace Spatie\Ray;

class Ray
{
    public function label(string $label): self { return $this; }

    public function color(string $color): self { return $this; }

    /** @param mixed ...$args */
    public function table(mixed ...$args): self { return $this; }
}

namespace {
    function ray(mixed ...$args): \Spatie\Ray\Ray { return new \Spatie\Ray\Ray(); }
}
