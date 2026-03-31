<?php

declare(strict_types=1);

namespace Xoops\SmartyExtensions\Test\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xoops\SmartyExtensions\Extension\AssetExtension;
use Xoops\SmartyExtensions\Test\Stubs\TemplateStub;

#[CoversClass(AssetExtension::class)]
final class AssetExtensionTest extends TestCase
{
    private AssetExtension $ext;

    protected function setUp(): void
    {
        $this->ext = new AssetExtension();
    }

    private function tpl(): object
    {
        return $this->createMock(TemplateStub::class);
    }

    // ── require_css ─────────────────────────────────────

    #[Test]
    public function requireCssRegistersStylesheet(): void
    {
        $this->ext->requireCss(['file' => 'style.css'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('href="style.css"', $html);
        $this->assertStringContainsString('rel="stylesheet"', $html);
    }

    #[Test]
    public function requireCssDeduplicates(): void
    {
        $this->ext->requireCss(['file' => 'style.css'], $this->tpl());
        $this->ext->requireCss(['file' => 'style.css'], $this->tpl());
        $this->ext->requireCss(['file' => 'other.css'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertSame(2, \substr_count($html, '<link'));
    }

    #[Test]
    public function requireCssReturnsEmptyForEmptyFile(): void
    {
        $result = $this->ext->requireCss(['file' => ''], $this->tpl());

        $this->assertSame('', $result);
    }

    #[Test]
    public function requireCssSupportsMediaAttribute(): void
    {
        $this->ext->requireCss(['file' => 'print.css', 'media' => 'print'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('media="print"', $html);
    }

    #[Test]
    public function requireCssDefaultsToMediaAll(): void
    {
        $this->ext->requireCss(['file' => 'style.css'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('media="all"', $html);
    }

    // ── require_js ──────────────────────────────────────

    #[Test]
    public function requireJsRegistersScript(): void
    {
        $this->ext->requireJs(['file' => 'app.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString('src="app.js"', $html);
        $this->assertStringContainsString('<script', $html);
    }

    #[Test]
    public function requireJsDeduplicates(): void
    {
        $this->ext->requireJs(['file' => 'app.js'], $this->tpl());
        $this->ext->requireJs(['file' => 'app.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame(1, \substr_count($html, '<script'));
    }

    #[Test]
    public function requireJsSupportsDeferAttribute(): void
    {
        $this->ext->requireJs(['file' => 'app.js', 'defer' => true], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString(' defer', $html);
    }

    #[Test]
    public function requireJsSupportsAsyncAttribute(): void
    {
        $this->ext->requireJs(['file' => 'analytics.js', 'async' => true], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString(' async', $html);
    }

    // ── last-write-wins for conflicting attributes ──────

    #[Test]
    public function requireCssLastWriteWinsForMedia(): void
    {
        $this->ext->requireCss(['file' => 'theme.css', 'media' => 'print'], $this->tpl());
        $this->ext->requireCss(['file' => 'theme.css', 'media' => 'screen'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertSame(1, \substr_count($html, '<link'));
        $this->assertStringContainsString('media="screen"', $html);
        $this->assertStringNotContainsString('media="print"', $html);
    }

    #[Test]
    public function requireJsLastWriteWinsForDefer(): void
    {
        $this->ext->requireJs(['file' => 'app.js'], $this->tpl());
        $this->ext->requireJs(['file' => 'app.js', 'defer' => true], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame(1, \substr_count($html, '<script'));
        $this->assertStringContainsString(' defer', $html);
    }

    // ── URL scheme safety ───────────────────────────────

    #[Test]
    public function requireJsBlocksDataScheme(): void
    {
        $this->ext->requireJs(['file' => 'data:text/javascript,alert(1)'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function requireJsBlocksJavascriptScheme(): void
    {
        $this->ext->requireJs(['file' => 'javascript:alert(1)'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function requireCssBlocksDataScheme(): void
    {
        $this->ext->requireCss(['file' => 'data:text/css,body{}'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function requireJsBlocksEntityEncodedScheme(): void
    {
        $this->ext->requireJs(['file' => 'javascript&#58;alert(1)'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function requireJsAllowsHttpsUrl(): void
    {
        $this->ext->requireJs(['file' => 'https://cdn.example.com/lib.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString('https://cdn.example.com/lib.js', $html);
    }

    #[Test]
    public function requireCssAllowsProtocolRelativeUrl(): void
    {
        $this->ext->requireCss(['file' => '//cdn.example.com/style.css'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('//cdn.example.com/style.css', $html);
    }

    #[Test]
    public function requireJsAllowsRelativePath(): void
    {
        $this->ext->requireJs(['file' => 'modules/news/js/app.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString('modules/news/js/app.js', $html);
    }

    #[Test]
    public function requireJsAllowsRelativeUrlWithColonInQueryString(): void
    {
        $this->ext->requireJs(['file' => '/asset.php?src=https://cdn.example.com/lib.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString('/asset.php?src=', $html);
    }

    #[Test]
    public function requireCssAllowsRelativeUrlWithColonInQueryString(): void
    {
        $this->ext->requireCss(['file' => '/proxy.php?url=https://fonts.example.com/style.css'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('/proxy.php?url=', $html);
    }

    #[Test]
    public function requireJsAllowsRelativeWithoutSlashAndColonInQuery(): void
    {
        $this->ext->requireJs(['file' => 'asset.php?src=https://cdn.example.com/lib.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertStringContainsString('asset.php?src=', $html);
    }

    #[Test]
    public function requireJsDecodesEntityEncodedHttpsUrl(): void
    {
        $this->ext->requireJs(['file' => 'https&#58;//cdn.example.com/lib.js'], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        // The decoded URL should be stored and rendered, not the entity-encoded original
        $this->assertStringContainsString('src="https://cdn.example.com/lib.js"', $html);
    }

    // ── flush behavior ──────────────────────────────────

    #[Test]
    public function flushCssClearsQueue(): void
    {
        $this->ext->requireCss(['file' => 'style.css'], $this->tpl());
        $this->ext->flushCss([], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function flushJsClearsQueue(): void
    {
        $this->ext->requireJs(['file' => 'app.js'], $this->tpl());
        $this->ext->flushJs([], $this->tpl());

        $html = $this->ext->flushJs([], $this->tpl());

        $this->assertSame('', $html);
    }

    #[Test]
    public function flushCssAssignReturnsStructuredArray(): void
    {
        $this->ext->requireCss(['file' => 'a.css', 'media' => 'screen'], $this->tpl());
        $this->ext->requireCss(['file' => 'b.css'], $this->tpl());

        $tpl = $this->createMock(TemplateStub::class);
        $tpl->expects($this->once())
            ->method('assign')
            ->with('styles', [
                ['file' => 'a.css', 'media' => 'screen'],
                ['file' => 'b.css', 'media' => 'all'],
            ]);

        $result = $this->ext->flushCss(['assign' => 'styles'], $tpl);

        $this->assertSame('', $result);
    }

    #[Test]
    public function flushJsAssignReturnsStructuredArray(): void
    {
        $this->ext->requireJs(['file' => 'x.js', 'defer' => true], $this->tpl());

        $tpl = $this->createMock(TemplateStub::class);
        $tpl->expects($this->once())
            ->method('assign')
            ->with('scripts', [
                ['file' => 'x.js', 'defer' => true, 'async' => false],
            ]);

        $result = $this->ext->flushJs(['assign' => 'scripts'], $tpl);

        $this->assertSame('', $result);
    }

    #[Test]
    public function flushCssEscapesOutput(): void
    {
        $this->ext->requireCss(['file' => 'style.css?v=1&t=2'], $this->tpl());

        $html = $this->ext->flushCss([], $this->tpl());

        $this->assertStringContainsString('style.css?v=1&amp;t=2', $html);
    }

    // ── getters ─────────────────────────────────────────

    #[Test]
    public function getFunctionsReturnsExpectedKeys(): void
    {
        $functions = $this->ext->getFunctions();

        $this->assertArrayHasKey('require_css', $functions);
        $this->assertArrayHasKey('require_js', $functions);
        $this->assertArrayHasKey('flush_css', $functions);
        $this->assertArrayHasKey('flush_js', $functions);
    }
}
