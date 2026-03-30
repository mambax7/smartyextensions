<?php

/**
 * PHPStan stub for Smarty 4 main class.
 *
 * Smarty 4 uses the global \Smarty class; Smarty 5 uses \Smarty\Smarty.
 * The Composer dependency resolves one or the other at install time,
 * so this stub ensures both are known to static analysis.
 */

class Smarty
{
    /**
     * @param string   $type     'modifier'|'function'|'block'
     * @param string   $name     Plugin name
     * @param callable $callback Plugin callback
     */
    public function registerPlugin(string $type, string $name, callable $callback): void {}

    /**
     * @param \Smarty\Extension\Base $extension
     */
    public function addExtension(object $extension): void {}
}
