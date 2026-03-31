<?php

declare(strict_types=1);

namespace Xoops\SmartyExtensions\Test\Stubs;

/**
 * Mockable interface for Smarty template objects in tests.
 *
 * Replaces getMockBuilder(\stdClass::class)->addMethods(['assign'])
 * which is deprecated in PHPUnit 11 and removed in PHPUnit 12.
 */
interface TemplateStub
{
    public function assign(string $name, mixed $value = null): void;

    /** @return array<string, mixed> */
    public function getTemplateVars(?string $name = null): mixed;
}
