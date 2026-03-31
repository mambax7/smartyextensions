# XOOPS Smarty Extensions

Domain-grouped Smarty template plugins for [XOOPS CMS](https://xoops.org). Provides modifiers, functions, and block handlers organized by concern, with automatic Smarty 4/5 dual-version support.

## Requirements

- PHP 8.2+
- Smarty 4.5+ or Smarty 5.0+

## Installation

```bash
composer require xoops/smartyextensions
```

## Usage

Register all extensions with a Smarty instance:

```php
use Xoops\SmartyExtensions\ExtensionRegistry;
use Xoops\SmartyExtensions\Extension\TextExtension;
use Xoops\SmartyExtensions\Extension\FormatExtension;
use Xoops\SmartyExtensions\Extension\NavigationExtension;
use Xoops\SmartyExtensions\Extension\DataExtension;
use Xoops\SmartyExtensions\Extension\SecurityExtension;
use Xoops\SmartyExtensions\Extension\FormExtension;
use Xoops\SmartyExtensions\Extension\XoopsCoreExtension;
use Xoops\SmartyExtensions\Extension\AssetExtension;

$registry = new ExtensionRegistry();
$registry->add(new TextExtension());
$registry->add(new FormatExtension());
$registry->add(new NavigationExtension());
$registry->add(new DataExtension());
$registry->add(new SecurityExtension($xoopsSecurity, $grouppermHandler));
$registry->add(new FormExtension($xoopsSecurity));
$registry->add(new XoopsCoreExtension());
$registry->add(new AssetExtension());

$registry->registerAll($smarty);
```

The registry detects the Smarty version and uses the appropriate registration path:
- **Smarty 4**: calls `registerPlugin()` on each extension
- **Smarty 5**: wraps each extension in `Smarty5Adapter` and calls `addExtension()`

## Extensions

### TextExtension

Text processing modifiers (pure PHP, no XOOPS dependencies).

| Plugin | Type | Description |
|--------|------|-------------|
| `excerpt` | modifier | Truncate text to length, breaking at word boundary |
| `truncate_words` | modifier | Truncate text to a word count |
| `nl2p` | modifier | Convert newlines to `<p>` paragraphs |
| `highlight_text` | modifier | Wrap search terms in `<span>` highlight |
| `reading_time` | modifier | Estimate reading time ("3 min read") |
| `pluralize` | modifier | Return singular or plural form based on count |
| `extract_hashtags` | modifier | Extract `#hashtag` strings from text |

### FormatExtension

Number, date, and display formatting (pure PHP).

| Plugin | Type | Description |
|--------|------|-------------|
| `format_date` | modifier | Format date/timestamp with PHP date format |
| `relative_time` | modifier | Human-readable relative time ("2 hours ago") |
| `format_currency` | modifier | Currency formatting via ICU NumberFormatter |
| `number_format` | modifier | Number with grouped thousands |
| `bytes_format` | modifier | Human-readable byte sizes ("1.5 MB") |
| `format_phone_number` | modifier | Format phone numbers as (XXX) XXX-XXXX |
| `gravatar` | modifier | Generate Gravatar URL from email |
| `datetime_diff` | function | Calculate difference between two dates |
| `get_current_year` | function | Return the current year |

### NavigationExtension

URLs, breadcrumbs, pagination, and UI components.

| Plugin | Type | Description |
|--------|------|-------------|
| `parse_url` | modifier | Parse URL into components |
| `strip_protocol` | modifier | Remove http/https from URL |
| `slugify` | modifier | Convert text to URL-friendly slug |
| `youtube_id` | modifier | Extract video ID from YouTube URL |
| `linkify` | modifier | Convert URLs in text to clickable links |
| `generate_url` | function | Build URL with query parameters |
| `generate_canonical_url` | function | Build canonical URL using XOOPS_URL |
| `url_segment` | function | Get a segment from the request URI |
| `social_share` | function | Social media share links/buttons |
| `render_breadcrumbs` | function | Bootstrap 5 breadcrumb navigation |
| `render_pagination` | function | Bootstrap 5 pagination controls |
| `render_qr_code` | function | QR code image tag |
| `render_alert` | function | Bootstrap 5 alert message |

### DataExtension

Data processing and file utilities (pure PHP).

| Plugin | Type | Description |
|--------|------|-------------|
| `array_filter` | modifier | Filter array of arrays by key/value |
| `array_sort` | modifier | Sort array by key and direction |
| `pretty_print_json` | modifier | Pretty-print JSON data |
| `get_file_size` | modifier | Human-readable file size |
| `get_mime_type` | modifier | Detect file MIME type |
| `is_image` | modifier | Check if filename has image extension |
| `strip_html_comments` | modifier | Remove HTML comments |
| `array_to_csv` | function | Convert array to CSV string |
| `base64_encode_file` | function | Base64-encode a file |
| `embed_pdf` | function | Render PDF in iframe |
| `generate_xml_sitemap` | function | Generate XML sitemap |
| `generate_meta_tags` | function | Generate HTML meta tags |
| `get_referrer` | function | Get HTTP referrer |
| `get_session_data` | function | Read session data by key |

### SecurityExtension

CSRF, permissions, sanitization, and hashing (uses XOOPS security when available).

| Plugin | Type | Description |
|--------|------|-------------|
| `sanitize_string` | modifier | Escape for safe HTML output |
| `sanitize_url` | modifier | Sanitize URL, block unsafe schemes |
| `sanitize_filename` | modifier | Remove unsafe characters from filename |
| `sanitize_string_for_xml` | modifier | Escape for XML content |
| `mask_email` | modifier | Partially hide email address |
| `obfuscate_text` | modifier | Convert to HTML entities |
| `hash_string` | modifier | Hash with configurable algorithm |
| `generate_csrf_token` | function | Generate XOOPS CSRF token HTML |
| `validate_csrf_token` | function | Validate XOOPS CSRF token |
| `has_user_permission` | function | Check user permission |
| `is_user_logged_in` | function | Check if user is logged in |
| `user_has_role` | function | Check user group membership |
| `xo_permission` | block | Conditionally render content by permission |

### FormExtension

Form rendering with automatic CSRF token injection (uses XOOPS security when available).

| Plugin | Type | Description |
|--------|------|-------------|
| `form_open` | function | Open form tag with CSRF token |
| `form_close` | function | Close form tag |
| `form_input` | function | Render input element |
| `create_button` | function | Render button with optional icon |
| `render_form_errors` | function | Render validation errors list |
| `validate_form` | function | Validate form data against rules |
| `validate_email` | function | Validate email address |
| `display_error` | function | Render error alert |

### XoopsCoreExtension

XOOPS-specific functions (config, users, modules, blocks).

| Plugin | Type | Description |
|--------|------|-------------|
| `translate` | modifier | Look up XOOPS language constant |
| `xo_get_config` | function | Get XOOPS config value |
| `xo_get_current_user` | function | Get current user as array |
| `xo_get_module_info` | function | Get module information |
| `xo_get_notifications` | function | Get user notifications |
| `xo_module_url` | function | Build module-relative URL |
| `xo_render_block` | function | Render a XOOPS block |
| `xo_render_menu` | function | Render module admin menu |
| `xo_avatar` | function | Render user avatar with Gravatar fallback |
| `xo_debug` | function | Debug dump (only in debug mode) |

### RayDebugExtension

[Ray](https://myray.app) desktop debugger integration (no-ops when Ray is unavailable).

| Plugin | Type | Description |
|--------|------|-------------|
| `ray` | modifier | Pass-through that sends value to Ray |
| `ray` | function | Send value/message to Ray |
| `ray_context` | function | Dump all template variables to Ray |
| `ray_dump` | function | Dump variable structure to Ray |
| `ray_table` | function | Send array to Ray table display |

### AssetExtension

Deduplicated CSS and JS inclusion (pure PHP, no XOOPS dependencies).

| Plugin | Type | Description |
|--------|------|-------------|
| `require_css` | function | Queue a stylesheet (deduplicated by file path, last-write-wins) |
| `require_js` | function | Queue a script (deduplicated, supports `defer`/`async`) |
| `flush_css` | function | Output all `<link>` tags and reset the queue |
| `flush_js` | function | Output all `<script>` tags and reset the queue |

Asset URLs are validated against a safe-scheme allowlist (`http://`, `https://`, `//`, relative paths). Unsafe schemes like `javascript:` and `data:` are silently rejected.

## Writing Custom Extensions

Extend `AbstractExtension` and override the getter methods:

```php
use Xoops\SmartyExtensions\AbstractExtension;

final class MyExtension extends AbstractExtension
{
    public function getModifiers(): array
    {
        return [
            'my_modifier' => $this->myModifier(...),
        ];
    }

    public function getFunctions(): array
    {
        return [
            'my_function' => $this->myFunction(...),
        ];
    }

    public function myModifier(string $value): string
    {
        return strtoupper($value);
    }

    public function myFunction(array $params, object $template): string
    {
        return 'Hello, ' . htmlspecialchars($params['name'] ?? 'World', ENT_QUOTES, 'UTF-8');
    }
}
```

## Testing

```bash
composer install
composer test
composer analyse
composer lint
```

## License

[GPL-2.0-or-later](LICENSE)
