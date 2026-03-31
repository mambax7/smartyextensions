<?php

declare(strict_types=1);

namespace Xoops\SmartyExtensions\Test\Extension;

require_once \dirname(__DIR__) . '/stubs/XoopsStubs.php';

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xoops\SmartyExtensions\Extension\SecurityExtension;

#[CoversClass(SecurityExtension::class)]
final class SecurityExtensionTest extends TestCase
{
    private SecurityExtension $ext;

    protected function setUp(): void
    {
        $this->ext = new SecurityExtension(
            new \XoopsSecurity(),
            new \XoopsGroupPermHandler(),
        );
    }

    // ──────────────────────────────────────────────
    // Registry counts
    // ──────────────────────────────────────────────

    #[Test]
    public function getModifiersReturnsSevenEntries(): void
    {
        $modifiers = $this->ext->getModifiers();
        $this->assertCount(7, $modifiers);
        $this->assertArrayHasKey('sanitize_string', $modifiers);
        $this->assertArrayHasKey('sanitize_url', $modifiers);
        $this->assertArrayHasKey('sanitize_filename', $modifiers);
        $this->assertArrayHasKey('sanitize_string_for_xml', $modifiers);
        $this->assertArrayHasKey('mask_email', $modifiers);
        $this->assertArrayHasKey('obfuscate_text', $modifiers);
        $this->assertArrayHasKey('hash_string', $modifiers);
    }

    #[Test]
    public function getFunctionsReturnsFiveEntries(): void
    {
        $functions = $this->ext->getFunctions();
        $this->assertCount(5, $functions);
        $this->assertArrayHasKey('generate_csrf_token', $functions);
        $this->assertArrayHasKey('validate_csrf_token', $functions);
        $this->assertArrayHasKey('has_user_permission', $functions);
        $this->assertArrayHasKey('is_user_logged_in', $functions);
        $this->assertArrayHasKey('user_has_role', $functions);
    }

    #[Test]
    public function getBlockHandlersReturnsOneEntry(): void
    {
        $blocks = $this->ext->getBlockHandlers();
        $this->assertCount(1, $blocks);
        $this->assertArrayHasKey('xo_permission', $blocks);
    }

    // ──────────────────────────────────────────────
    // Modifier: sanitize_string
    // ──────────────────────────────────────────────

    #[Test]
    public function sanitizeStringEscapesHtmlTags(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(1)&lt;/script&gt;',
            $this->ext->sanitizeString('<script>alert(1)</script>'),
        );
    }

    #[Test]
    public function sanitizeStringEscapesQuotes(): void
    {
        $this->assertSame(
            '&quot;hello&quot; &amp; &#039;world&#039;',
            $this->ext->sanitizeString('"hello" & \'world\''),
        );
    }

    // ──────────────────────────────────────────────
    // Modifier: sanitize_url
    // ──────────────────────────────────────────────

    #[Test]
    public function sanitizeUrlAllowsHttpScheme(): void
    {
        $this->assertSame('https://example.com/path', $this->ext->sanitizeUrl('https://example.com/path'));
    }

    #[Test]
    public function sanitizeUrlAllowsRelativePath(): void
    {
        $this->assertSame('/images/photo.jpg', $this->ext->sanitizeUrl('/images/photo.jpg'));
    }

    #[Test]
    public function sanitizeUrlAllowsHashFragment(): void
    {
        $this->assertSame('#section', $this->ext->sanitizeUrl('#section'));
    }

    #[Test]
    public function sanitizeUrlAllowsRelativeWithoutScheme(): void
    {
        $this->assertSame('page.html', $this->ext->sanitizeUrl('page.html'));
    }

    #[Test]
    public function sanitizeUrlBlocksJavascriptScheme(): void
    {
        $this->assertSame('', $this->ext->sanitizeUrl('javascript:alert(1)'));
    }

    #[Test]
    public function sanitizeUrlBlocksEntityEncodedJavascript(): void
    {
        $this->assertSame('', $this->ext->sanitizeUrl('javascript&#58;alert(1)'));
    }

    #[Test]
    public function sanitizeUrlBlocksEntityEncodedJavascriptHex(): void
    {
        $this->assertSame('', $this->ext->sanitizeUrl('javascript&#x3a;alert(1)'));
    }

    // ──────────────────────────────────────────────
    // Modifier: sanitize_filename
    // ──────────────────────────────────────────────

    #[Test]
    public function sanitizeFilenameStripsUnsafeChars(): void
    {
        $this->assertSame('report-2024_v2.pdf', $this->ext->sanitizeFilename('report-2024_v2.pdf'));
    }

    #[Test]
    public function sanitizeFilenameRemovesPathTraversal(): void
    {
        $this->assertSame('....etcpasswd', $this->ext->sanitizeFilename('../../etc/passwd'));
    }

    // ──────────────────────────────────────────────
    // Modifier: sanitize_string_for_xml
    // ──────────────────────────────────────────────

    #[Test]
    public function sanitizeStringForXmlEscapesAngleBrackets(): void
    {
        $this->assertSame('&lt;tag&gt;', $this->ext->sanitizeStringForXml('<tag>'));
    }

    // ──────────────────────────────────────────────
    // Modifier: mask_email
    // ──────────────────────────────────────────────

    #[Test]
    public function maskEmailHidesLocalPart(): void
    {
        $result = $this->ext->maskEmail('username@example.com');
        $this->assertStringStartsWith('us', $result);
        $this->assertStringContainsString('***', $result);
        $this->assertStringEndsWith('@example.com', $result);
    }

    #[Test]
    public function maskEmailShortLocalPart(): void
    {
        $result = $this->ext->maskEmail('ab@example.com');
        $this->assertStringEndsWith('@example.com', $result);
        $this->assertStringStartsWith('a', $result);
    }

    #[Test]
    public function maskEmailReturnsInvalidInputUnchanged(): void
    {
        $this->assertSame('not-an-email', $this->ext->maskEmail('not-an-email'));
    }

    // ──────────────────────────────────────────────
    // Modifier: obfuscate_text
    // ──────────────────────────────────────────────

    #[Test]
    public function obfuscateTextConvertsToEntities(): void
    {
        $result = $this->ext->obfuscateText('AB');
        $this->assertSame('&#65;&#66;', $result);
    }

    #[Test]
    public function obfuscateTextEmptyString(): void
    {
        $this->assertSame('', $this->ext->obfuscateText(''));
    }

    // ──────────────────────────────────────────────
    // Modifier: hash_string
    // ──────────────────────────────────────────────

    #[Test]
    public function hashStringDefaultsSha256(): void
    {
        $this->assertSame(\hash('sha256', 'test'), $this->ext->hashString('test'));
    }

    #[Test]
    public function hashStringMd5(): void
    {
        $this->assertSame(\hash('md5', 'test'), $this->ext->hashString('test', 'md5'));
    }

    #[Test]
    public function hashStringReturnsEmptyForInvalidAlgo(): void
    {
        $this->assertSame('', $this->ext->hashString('test', 'not-a-real-algo'));
    }

    // ──────────────────────────────────────────────
    // Function: generate_csrf_token
    // ──────────────────────────────────────────────

    #[Test]
    public function generateCsrfTokenReturnsHtml(): void
    {
        $tpl = $this->createTemplateMock();
        $result = $this->ext->generateCsrfToken([], $tpl);
        $this->assertStringContainsString('input', $result);
        $this->assertStringContainsString('token', $result);
    }

    #[Test]
    public function generateCsrfTokenAssignsToVariable(): void
    {
        $tpl = $this->createTemplateMock();
        $tpl->expects($this->once())
            ->method('assign')
            ->with('myToken', $this->stringContains('input'));

        $result = $this->ext->generateCsrfToken(['assign' => 'myToken'], $tpl);
        $this->assertSame('', $result);
    }

    // ──────────────────────────────────────────────
    // Function: validate_csrf_token
    // ──────────────────────────────────────────────

    #[Test]
    public function validateCsrfTokenReturnsOneOnSuccess(): void
    {
        $tpl = $this->createTemplateMock();
        $result = $this->ext->validateCsrfToken([], $tpl);
        $this->assertSame('1', $result);
    }

    #[Test]
    public function validateCsrfTokenAssignsBoolOnAssign(): void
    {
        $tpl = $this->createTemplateMock();
        $tpl->expects($this->once())
            ->method('assign')
            ->with('isValid', true);

        $result = $this->ext->validateCsrfToken(['assign' => 'isValid'], $tpl);
        $this->assertSame('', $result);
    }

    // ──────────────────────────────────────────────
    // Function: is_user_logged_in (no user set)
    // ──────────────────────────────────────────────

    #[Test]
    public function isUserLoggedInReturnEmptyWhenNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $result = $this->ext->isUserLoggedIn([], $tpl);
        $this->assertSame('', $result);
    }

    #[Test]
    public function isUserLoggedInAssignsFalseWhenNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $tpl->expects($this->once())
            ->method('assign')
            ->with('loggedIn', false);

        $this->ext->isUserLoggedIn(['assign' => 'loggedIn'], $tpl);
    }

    // ──────────────────────────────────────────────
    // Function: has_user_permission (no user)
    // ──────────────────────────────────────────────

    #[Test]
    public function hasUserPermissionReturnEmptyWhenNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $result = $this->ext->hasUserPermission(['permission' => 'module_admin'], $tpl);
        $this->assertSame('', $result);
    }

    // ──────────────────────────────────────────────
    // Function: user_has_role (no user)
    // ──────────────────────────────────────────────

    #[Test]
    public function userHasRoleReturnEmptyWhenNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $result = $this->ext->userHasRole(['role' => '1'], $tpl);
        $this->assertSame('', $result);
    }

    // ──────────────────────────────────────────────
    // Block: xo_permission
    // ──────────────────────────────────────────────

    #[Test]
    public function xoPermissionReturnsEmptyOnOpenTag(): void
    {
        $tpl = $this->createTemplateMock();
        $repeat = true;
        $result = $this->ext->xoPermission([], null, $tpl, $repeat);
        $this->assertSame('', $result);
    }

    #[Test]
    public function xoPermissionReturnsContentWhenNoConstraints(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $repeat = false;
        $result = $this->ext->xoPermission([], 'Hello World', $tpl, $repeat);
        $this->assertSame('Hello World', $result);
    }

    #[Test]
    public function xoPermissionReturnsEmptyWhenLoggedInRequiredButNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $repeat = false;
        $result = $this->ext->xoPermission(['logged_in' => true], 'Secret', $tpl, $repeat);
        $this->assertSame('', $result);
    }

    #[Test]
    public function xoPermissionReturnsEmptyWhenPermissionRequiredButNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $repeat = false;
        $result = $this->ext->xoPermission(['require' => 'module_admin'], 'Admin stuff', $tpl, $repeat);
        $this->assertSame('', $result);
    }

    #[Test]
    public function xoPermissionReturnsEmptyWhenGroupRequiredButNoUser(): void
    {
        $GLOBALS['xoopsUser'] = null;
        $tpl = $this->createTemplateMock();
        $repeat = false;
        $result = $this->ext->xoPermission(['group' => '1'], 'Group stuff', $tpl, $repeat);
        $this->assertSame('', $result);
    }

    // ──────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&\Xoops\SmartyExtensions\Test\Stubs\TemplateStub
     */
    private function createTemplateMock(): object
    {
        return $this->createMock(\Xoops\SmartyExtensions\Test\Stubs\TemplateStub::class);
    }
}
