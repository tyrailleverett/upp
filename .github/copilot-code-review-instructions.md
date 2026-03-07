# Code Review Instructions

You are reviewing a Laravel 12 SaaS application using PHP 8.4, Inertia.js v2, React 19, Tailwind CSS v4, and Stripe (Cashier v16). Conduct a thorough security-focused code review.

## Security Review (OWASP-Mapped)

### Injection (SQL, Command, XSS)
- Flag any use of `DB::raw()`, `whereRaw()`, or raw expressions without parameter binding
- Flag `dangerouslySetInnerHTML` usage in React components
- Flag string concatenation in database queries — require parameterized queries
- Flag unsanitized user input rendered in Blade or Inertia responses

### Broken Authentication
- Verify auth routes use `auth` middleware and sensitive routes use `verified` middleware
- Verify two-factor challenge routes check session state properly
- Flag any hardcoded credentials, tokens, or secrets
- Verify Socialite callbacks validate the provider response

### Broken Access Control
- Every controller action accessing a resource must check authorization via Policy or Gate
- Flag missing `$this->authorize()` or policy checks on show/update/destroy actions
- Verify `$fillable` or `$guarded` is defined on all models — flag mass assignment risks
- Check that subscription/plan middleware (`subscribed`, `plan-allows`) protects paid features

### Security Misconfiguration
- Flag `env()` calls outside of `config/` files — must use `config()` helper
- Flag `.env`, credentials, or secret files being committed
- Verify Stripe webhook routes are excluded from CSRF but validate signatures

### Cryptographic Failures
- Flag any custom encryption — use Laravel's built-in `encrypt()`/`decrypt()`
- Verify sensitive model attributes are in the `$hidden` array
- Flag logging of passwords, tokens, or PII

### SSRF / Unsafe Requests
- Flag unvalidated URLs passed to HTTP client or file_get_contents
- Verify file upload validation includes MIME type and size checks

## Architecture Review
- Every change must include or update tests (Pest)
- Controllers must use Form Request classes for validation, not inline rules
- Business logic belongs in Action classes, not controllers
- Prefer `Model::query()` over `DB::` facade
- Use eager loading to prevent N+1 queries
