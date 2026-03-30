<?php

/**
 * PHPStan stubs for XOOPS framework classes used by smartyextensions.
 *
 * These stubs exist solely so static analysis can resolve types.
 * They are NOT loaded at runtime.
 */

class XoopsUser
{
    /** @return mixed */
    public function getVar(string $name, string $format = 's') {}

    /** @return list<string> */
    public function getGroups(): array { return []; }

    public function isAdmin(int $moduleId = 0): bool { return false; }

    public function hasPermission(string $permission): bool { return false; }
}

class XoopsModule
{
    /** @return mixed */
    public function getVar(string $name, string $format = 's') {}

    /** @return list<array{link: string, title: string}> */
    public function getAdminMenu(): array { return []; }

    public static function getByDirname(string $dirname): ?self { return null; }
}

class XoopsSecurity
{
    public function getTokenHTML(): string { return ''; }

    public function check(bool $clearIfValid = true): bool { return false; }
}

class XoopsGroupPermHandler
{
}

/**
 * @return object
 */
function xoops_getHandler(string $name, bool $optional = false) {}
