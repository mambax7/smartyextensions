<?php

/**
 * PHPStan stub for XOOPS Debugbar RayLogger.
 */

namespace XoopsModules\Debugbar;

class RayLogger
{
    public static function getInstance(): self { return new self(); }

    public function isEnabled(): bool { return false; }
}
