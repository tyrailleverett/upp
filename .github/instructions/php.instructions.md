---
applyTo: "**/*.php"
---

# PHP & Laravel Review (PHP 8.4, Laravel 12)

## PHP Standards
- All methods must have explicit return type declarations and parameter type hints
- Use PHP 8 constructor property promotion — no empty constructors
- Use curly braces for all control structures, even single-line bodies
- Enum keys must be TitleCase (e.g., `Monthly`, `FavoritePerson`)
- Prefer PHPDoc blocks over inline comments; only use inline for exceptionally complex logic

## Laravel 12 Conventions
- Middleware is registered in `bootstrap/app.php`, not `app/Http/Kernel.php`
- Model casts use the `casts()` method, not the `$casts` property
- Use `php artisan make:` commands for scaffolding — don't manually create migration/model/controller boilerplate
- Use `config()` helper, never `env()` outside of config files

## Controllers & Validation
- Controllers must delegate business logic to Action classes in `app/Actions/`
- All validation must use Form Request classes — flag inline `$request->validate()` calls
- Check that Form Requests use the same format (array vs string rules) as sibling requests

## Eloquent & Database
- Prefer `Model::query()` over `DB::` facade
- Relationships must have return type hints
- Flag N+1 query risks — require eager loading with `with()` or `load()`
- Flag raw queries without parameter binding
- New models must include a factory and seeder

## Auth & Authorization
- Routes accessing user resources must use Policy authorization
- Protected routes must use appropriate middleware (`auth`, `verified`, `subscribed`)
- Flag any direct user ID comparisons — use Policies instead

## Testing
- Every change must include or update Pest tests
- Use model factories with appropriate states — don't manually build models in tests
- Most tests should be feature tests, not unit tests
