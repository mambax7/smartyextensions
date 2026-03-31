<?php

declare(strict_types=1);

namespace Xoops\SmartyExtensions\Test\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xoops\SmartyExtensions\Extension\RayDebugExtension;

#[CoversClass(RayDebugExtension::class)]
final class RayDebugExtensionTest extends TestCase
{
    private RayDebugExtension $ext;

    protected function setUp(): void
    {
        $this->ext = new RayDebugExtension();
    }

    // ──────────────────────────────────────────────
    // Registry counts
    // ──────────────────────────────────────────────

    #[Test]
    public function getFunctionsReturnsFourEntries(): void
    {
        $functions = $this->ext->getFunctions();
        $this->assertCount(4, $functions);
        $this->assertSame(
            ['ray', 'ray_context', 'ray_dump', 'ray_table'],
            \array_keys($functions),
        );
    }

    #[Test]
    public function getModifiersReturnsOneEntry(): void
    {
        $modifiers = $this->ext->getModifiers();
        $this->assertCount(1, $modifiers);
        $this->assertSame(['ray'], \array_keys($modifiers));
    }

    #[Test]
    public function getBlockHandlersReturnsEmpty(): void
    {
        $this->assertSame([], $this->ext->getBlockHandlers());
    }

    // ──────────────────────────────────────────────
    // No-op behavior (Ray/Debugbar unavailable)
    // ──────────────────────────────────────────────

    #[Test]
    public function rayReturnsEmptyWhenRayUnavailable(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->ray(['msg' => 'test'], $tpl));
    }

    #[Test]
    public function rayReturnsEmptyWithValueParam(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->ray(['value' => 'hello', 'label' => 'L', 'color' => 'green'], $tpl));
    }

    #[Test]
    public function rayReturnsEmptyWhenNoDataProvided(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->ray([], $tpl));
    }

    #[Test]
    public function rayContextReturnsEmptyWhenRayUnavailable(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->rayContext([], $tpl));
    }

    #[Test]
    public function rayDumpReturnsEmptyWhenRayUnavailable(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->rayDump(['value' => ['a' => 1]], $tpl));
    }

    #[Test]
    public function rayTableReturnsEmptyWhenRayUnavailable(): void
    {
        $tpl = $this->createTemplateMock();
        $this->assertSame('', $this->ext->rayTable(['value' => [['a' => 1]]], $tpl));
    }

    #[Test]
    public function rayModifierReturnsOriginalValueWhenRayUnavailable(): void
    {
        $this->assertSame('hello', $this->ext->rayModifier('hello'));
    }

    #[Test]
    public function rayModifierReturnsOriginalValueWithLabel(): void
    {
        $this->assertSame('hello', $this->ext->rayModifier('hello', 'My Label'));
    }

    #[Test]
    public function rayModifierPreservesArrayValue(): void
    {
        $arr = ['key' => 'value'];
        $this->assertSame($arr, $this->ext->rayModifier($arr));
    }

    #[Test]
    public function rayModifierPreservesIntValue(): void
    {
        $this->assertSame(42, $this->ext->rayModifier(42));
    }

    #[Test]
    public function rayModifierPreservesNullValue(): void
    {
        $this->assertNull($this->ext->rayModifier(null));
    }

    #[Test]
    public function rayModifierPreservesBoolValue(): void
    {
        $this->assertTrue($this->ext->rayModifier(true));
    }

    // ──────────────────────────────────────────────
    // ray_context helper logic (via reflection)
    // ──────────────────────────────────────────────

    #[Test]
    public function applyExclusionsRemovesExactKey(): void
    {
        $vars = ['title' => 'Hello', 'items' => [1, 2], 'flag' => true];
        $filtered = $this->callPrivate('applyExclusions', [$vars, 'flag']);
        $this->assertArrayNotHasKey('flag', $filtered);
        $this->assertArrayHasKey('title', $filtered);
        $this->assertArrayHasKey('items', $filtered);
    }

    #[Test]
    public function applyExclusionsRemovesWildcardPrefix(): void
    {
        $vars = ['xoops_url' => 'x', 'xoops_root' => 'y', 'title' => 'Hello'];
        $filtered = $this->callPrivate('applyExclusions', [$vars, 'xoops_*']);
        $this->assertArrayNotHasKey('xoops_url', $filtered);
        $this->assertArrayNotHasKey('xoops_root', $filtered);
        $this->assertArrayHasKey('title', $filtered);
    }

    #[Test]
    public function applyExclusionsHandlesMultiplePatterns(): void
    {
        $vars = ['xoops_url' => 'x', 'title' => 'Hello', 'flag' => true];
        $filtered = $this->callPrivate('applyExclusions', [$vars, 'xoops_*,flag']);
        $this->assertArrayNotHasKey('xoops_url', $filtered);
        $this->assertArrayNotHasKey('flag', $filtered);
        $this->assertArrayHasKey('title', $filtered);
    }

    #[Test]
    public function applyExclusionsHandlesNonMatchingPattern(): void
    {
        $vars = ['title' => 'Hello'];
        $filtered = $this->callPrivate('applyExclusions', [$vars, 'missing']);
        $this->assertSame(['title' => 'Hello'], $filtered);
    }

    #[Test]
    public function formatValueFormatsObject(): void
    {
        $this->assertSame('{stdClass}', $this->callPrivate('formatValue', [new \stdClass()]));
    }

    #[Test]
    public function formatValueFormatsArray(): void
    {
        $this->assertSame('Array[3]', $this->callPrivate('formatValue', [[1, 2, 3]]));
    }

    #[Test]
    public function formatValueFormatsBoolTrue(): void
    {
        $this->assertSame('true', $this->callPrivate('formatValue', [true]));
    }

    #[Test]
    public function formatValueFormatsBoolFalse(): void
    {
        $this->assertSame('false', $this->callPrivate('formatValue', [false]));
    }

    #[Test]
    public function formatValueFormatsNull(): void
    {
        $this->assertSame('NULL', $this->callPrivate('formatValue', [null]));
    }

    #[Test]
    public function formatValueTruncatesLongString(): void
    {
        $long = \str_repeat('a', 250);
        $result = $this->callPrivate('formatValue', [$long]);
        $this->assertSame(\substr($long, 0, 200) . '...', $result);
    }

    #[Test]
    public function formatValuePassesThroughShortString(): void
    {
        $this->assertSame('short', $this->callPrivate('formatValue', ['short']));
    }

    #[Test]
    public function formatValuePassesThroughInt(): void
    {
        $this->assertSame(42, $this->callPrivate('formatValue', [42]));
    }

    #[Test]
    public function normalizeValuesFormatsAllEntries(): void
    {
        $vars = ['name' => 'test', 'obj' => new \stdClass(), 'arr' => [1]];
        $result = $this->callPrivate('normalizeValues', [$vars]);
        $this->assertSame('test', $result['name']);
        $this->assertSame('{stdClass}', $result['obj']);
        $this->assertSame('Array[1]', $result['arr']);
    }

    // ──────────────────────────────────────────────
    // Language fallback
    // ──────────────────────────────────────────────

    #[Test]
    public function langReturnsFallbackWhenConstantUndefined(): void
    {
        $this->assertSame(
            'Template Context',
            $this->callPrivate('lang', ['_MD_DEBUGBAR_RAY_TEMPLATE_CONTEXT', 'Template Context']),
        );
    }

    #[Test]
    public function langReturnsConstantValueWhenDefined(): void
    {
        // Define a test constant
        if (!\defined('_TEST_RAY_CONST')) {
            \define('_TEST_RAY_CONST', 'From Constant');
        }
        $this->assertSame(
            'From Constant',
            $this->callPrivate('lang', ['_TEST_RAY_CONST', 'Fallback']),
        );
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&object
     */
    private function createTemplateMock(): object
    {
        return $this->createMock(\Xoops\SmartyExtensions\Test\Stubs\TemplateStub::class);
    }

    private function callPrivate(string $method, array $args): mixed
    {
        $ref = new \ReflectionMethod(RayDebugExtension::class, $method);

        return $ref->invoke($this->ext, ...$args);
    }
}
