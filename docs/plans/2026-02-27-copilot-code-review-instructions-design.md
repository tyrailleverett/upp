# Design: GitHub Copilot Code Review Instructions

## Goal

Create comprehensive, security-first code review instructions for GitHub Copilot that match the quality of tools like CodeRabbit and Greptile, tailored to this project's Laravel 12 SaaS stack.

## Approach

**OWASP-Anchored** — Main file focuses on security mapped to OWASP Top 10 with stack-specific checks. Path-specific instruction files extend coverage for PHP, TypeScript, and database domains.

## File Structure

| File | Scope | Focus | ~Chars |
|------|-------|-------|--------|
| `.github/copilot-code-review-instructions.md` | All PRs | Security (OWASP) + architecture | ~2,200 |
| `.github/instructions/php.instructions.md` | `**/*.php` | PHP 8.4 / Laravel 12 conventions | ~1,700 |
| `.github/instructions/typescript.instructions.md` | `**/*.{ts,tsx}` | React 19 / Inertia v2 / a11y | ~1,500 |
| `.github/instructions/database.instructions.md` | `database/**/*.php` | Migration safety / query perf | ~950 |

## Key Design Decisions

1. **Security-first allocation** — Main file dedicates ~60% to OWASP-mapped security checks, remainder for architecture.
2. **Version-specific** — References exact versions (PHP 8.4, Laravel 12, React 19) for precise reviews. Will need updating on upgrades.
3. **Path-specific files** — Uses `applyTo` frontmatter to scope rules to relevant file types, maximizing the 4,000 char-per-file budget.
4. **Project-specific rules** — Checks reference actual project patterns (Action classes, Wayfinder imports, Cashier methods, Form Requests).

## Security Categories (Main File)

- Injection (SQL, XSS, command)
- Broken Authentication (Fortify, Socialite, 2FA)
- Broken Access Control (Policies, middleware, mass assignment)
- Security Misconfiguration (env(), secrets, CSRF exemptions)
- Cryptographic Failures (custom encryption, hidden attributes, PII logging)
- SSRF / Unsafe Requests (unvalidated URLs, file uploads)

## Implementation

4 files to create, no existing files to modify. Straightforward write operations.
