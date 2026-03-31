# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-03-31

### New Features

* **AssetExtension** — deduplicated CSS and JS inclusion for templates
  - `require_css` / `require_js` queue assets by file path (last-write-wins for conflicting attributes)
  - `flush_css` / `flush_js` output all queued `<link>`/`<script>` tags and clear the queue
  - `assign` mode returns structured arrays with full metadata (file, media, defer, async)
  - URL scheme allowlist: `http://`, `https://`, protocol-relative, and relative paths only
  - Entity-encoded URLs are decoded before validation and stored decoded
  - Colons in query strings (e.g., `asset.php?src=https://...`) are correctly allowed

## [1.0.1] - 2026-03-31

### Security

* **sanitize_url**: decode HTML entities before scheme check to block entity-encoded bypass (e.g. `javascript&#58;alert(1)`)
* **sanitize_url**: split `mailto:` into its own regex branch so `mailto:user@example.com` is correctly allowed
* **has_user_permission / xo_permission**: rewired to use injected `XoopsGroupPermHandler->checkRight()` instead of non-existent `XoopsUser->hasPermission()`; removed fake `hasPermission()` from test stubs
* **base64_encode_file**: enforce `XOOPS_ROOT_PATH` / `DOCUMENT_ROOT` path boundary; fail closed when neither is set
* **generate_canonical_url**: refuse to build URL from `HTTP_HOST` when `XOOPS_URL` is undefined — prevents host-header poisoning

### Bug Fixes

* **prettyPrintJson**: handle `json_encode()` returning `false` on invalid UTF-8 instead of crashing with TypeError
* **relativeTime**: respect `DateInterval->invert` so future dates show "from now" instead of "ago"
* **Smarty5Adapter**: return proper `FunctionHandlerInterface` / `BlockHandlerInterface` instead of raw callables — fixes covariance errors with Smarty 5

### Changed

* Package renamed from `xoops/smarty-extensions` to `xoops/smartyextensions` for consistency with namespace `Xoops\SmartyExtensions`
* PHPStan lowered from level max to level 5 (level max is impractical for Smarty's untyped `array $params` convention)
* `fputcsv()` call now passes explicit `escape` parameter to suppress PHP 8.4 deprecation

### Infrastructure

* Add PHPStan stubs for XOOPS classes, Smarty 4/5, Ray, and Debugbar RayLogger
* Add `require-dev` (phpstan, phpunit, phpcs) and composer scripts (`test`, `analyse`, `lint`)
* Fix `phpunit.xml.dist` test directory path (`./tests/unit` -> `./tests`) and bootstrap
* Fix test bootstrap to work standalone (not just within `xoops_lib/vendor/`)
* Fix `ExtensionRegistryTest` for Smarty 4/5 dual compatibility
* Fix test stub `SmartyExtensionBaseStub` signatures to match Smarty 5 return types
* Replace all `MockBuilder::addMethods()` calls with `TemplateStub` interface — eliminates 40 PHPUnit deprecations and forward-compatible with PHPUnit 12
* Remove legacy `pr_tests0.yml` workflow (XMF leftover testing PHP 7.4/8.0/8.1)
* Update `.coderabbit.yaml` from PHP 7.4 to PHP 8.2+
* Update `sonar-project.properties` from XMF to smartyextensions
* Remove stale `stubs/` and `phpstan-baseline.neon` references from `.gitattributes` and `.scrutinizer.yml`
* Bump `shivammathur/setup-php` to 2.37.0, `actions/cache` to 5.0.4, `JetBrains/qodana-action` to 2025.3.2, `codecov/codecov-action` to 6.0.0

### Documentation

* Write comprehensive README with plugin reference tables and registration example
* Write TUTORIAL.md with practical template examples for all extensions
* Add quick start example, XOOPS dependency matrix, best practices, and troubleshooting sections
* Document direct-output vs assign behavior, HTML-output warnings, and failure modes
* Replace XMF changelog with smartyextensions-specific changelog

## [1.0.0] - 2026-03-30

### Initial Release

Extracted from XOOPS Core 2.5 (`htdocs/xoops_lib/vendor/xoops/smartyextensions/`) into a standalone Composer package.

### Features

* **AbstractExtension** base class with `getModifiers()`, `getFunctions()`, `getBlockHandlers()`, and Smarty 4 `register()` method
* **ExtensionRegistry** — central registry that auto-detects Smarty 4 vs 5 and uses the appropriate registration path
* **Smarty5Adapter** — wraps any AbstractExtension into a `\Smarty\Extension\Base` subclass for Smarty 5 compatibility
* **TextExtension** — `excerpt`, `truncate_words`, `nl2p`, `highlight_text`, `reading_time`, `pluralize`, `extract_hashtags`
* **FormatExtension** — `format_date`, `relative_time`, `format_currency`, `number_format`, `bytes_format`, `format_phone_number`, `gravatar`, `datetime_diff`, `get_current_year`
* **NavigationExtension** — `generate_url`, `generate_canonical_url`, `url_segment`, `social_share`, `render_breadcrumbs`, `render_pagination`, `render_qr_code`, `render_alert`, `parse_url`, `strip_protocol`, `slugify`, `youtube_id`, `linkify`
* **DataExtension** — `array_to_csv`, `base64_encode_file`, `embed_pdf`, `generate_xml_sitemap`, `generate_meta_tags`, `get_referrer`, `get_session_data`, `array_filter`, `array_sort`, `pretty_print_json`, `get_file_size`, `get_mime_type`, `is_image`, `strip_html_comments`
* **SecurityExtension** — `generate_csrf_token`, `validate_csrf_token`, `has_user_permission`, `is_user_logged_in`, `user_has_role`, `sanitize_string`, `sanitize_url`, `sanitize_filename`, `sanitize_string_for_xml`, `mask_email`, `obfuscate_text`, `hash_string`, `xo_permission` block handler
* **FormExtension** — `form_open` (with automatic CSRF token injection), `form_close`, `form_input`, `create_button`, `render_form_errors`, `validate_form`, `validate_email`, `display_error`
* **XoopsCoreExtension** — `xo_get_config`, `xo_get_current_user`, `xo_get_module_info`, `xo_get_notifications`, `xo_module_url`, `xo_render_block`, `xo_render_menu`, `xo_avatar`, `xo_debug`, `translate` modifier
* **RayDebugExtension** — `ray`, `ray_context`, `ray_dump`, `ray_table` functions and `ray` modifier (silent no-op when Ray unavailable)

### Infrastructure

* PSR-4 autoloading under `Xoops\SmartyExtensions\`
* PHPUnit 11 test suite with full coverage
* PHPStan static analysis
* PHP_CodeSniffer PSR-12 enforcement
* GitHub Actions CI matrix: PHP 8.2-8.5 with lowest-deps and coverage runs
* Codecov, SonarCloud, and Qodana integrations
* Dependabot and Renovate for automated dependency updates
* CodeRabbit AI code review configuration
