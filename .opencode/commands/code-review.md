---
description: Perform a CodeRabbit-style code review of all staged and changed files. Reports inline findings with severity ratings covering security, performance, correctness, architecture, testing, Laravel SaaS conventions, and frontend (TypeScript/React/Inertia).
---

Perform a thorough, automated code review of all staged and changed files in the current working tree. Think like a senior Laravel SaaS engineer. Be precise, direct, and unsparing — this review exists to catch real problems before they land in main.

## Project Context

This is a **Laravel SaaS application** built on PHP 8.4 targeting Laravel 12.x with an Inertia.js + React frontend. Key tooling:

- **PHP** ^8.4 — strict types, constructor property promotion, named arguments, enums, fibers
- **Laravel** ^12.0
- **Frontend** — Inertia.js (`@inertiajs/react`) + React 19 + TypeScript + Vite
- **UI** — shadcn/ui components, Radix UI, Tailwind CSS v4
- **Forms** — `react-hook-form` + `zod` for schema validation
- **Auth** — Laravel Fortify + Socialite; authorization via `spatie/laravel-permission`
- **Testing** — Pest 4 + `pestphp/pest-plugin-laravel` + `pestphp/pest-plugin-browser` (Playwright)
- **Static analysis** — Larastan 3 (PHPStan level strict)
- **Linting** — Duster + Rector; formatting — Pint + Biome

## Severity Scale

Every finding must carry one of the following labels:

| Label | When to use |
|---|---|
| 🔴 **Critical** | Security flaw, data-loss risk, or exploit vector — block merge |
| 🟠 **High** | Correctness bug or major quality regression |
| 🟡 **Medium** | Maintainability or performance concern |
| 🟢 **Low** | Minor convention or style deviation |
| 💡 **Suggestion** | Optional improvement; not a defect |

## Output Format

Report each finding on its own line using this exact format:

🟠 High · src/Foo.php:42 — Brief, specific description of the issue and why it matters.

Group findings by file. After all findings, output a **Summary** block:

| Severity | Count |
|---|---|
| 🔴 Critical | 0 |
| 🟠 High | 0 |
| 🟡 Medium | 0 |
| 🟢 Low | 0 |
| 💡 Suggestion | 0 |

Verdict: ✅ Approved / ⚠️ Approved with suggestions / 🚫 Changes required

Set the verdict to:
- ✅ **Approved** — no Critical or High findings
- ⚠️ **Approved with suggestions** — only Medium, Low, or Suggestions
- 🚫 **Changes required** — any Critical or High findings

---

## Workflow

Follow these steps in order. Do not skip any step.

### Step 1 — Collect Changed Files

The following files have been modified (staged and unstaged):

!`git diff --staged --name-only && git diff --name-only`

Filter to PHP files, Blade templates, and TypeScript/React files (`.ts`, `.tsx`). Skip `vendor/`, `node_modules/`, lock files, and generated stubs.

For each relevant file, read its full contents so you have line numbers for precise references.

### Step 2 — Run Each Review Category

Apply every category below to every changed file. If a category doesn't apply to a given file (e.g., testing rules don't apply to a migration), skip it silently.

---

#### Category 1 — PHP & Code Standards

- **`declare(strict_types=1)`** — Must be present in every PHP file, immediately after the opening `<?php` tag.
- **`final` classes** — Every class must be `final` unless extension is explicitly required and justified.
- **Constructor property promotion** — Use promoted properties in `__construct()`. Flag any constructor that manually assigns properties that could be promoted. Flag empty zero-parameter constructors that aren't `private`.
- **Return types** — Every method and function must have an explicit return type. Flag missing return types, including `void`.
- **Parameter type hints** — Every parameter must be typed. Flag missing types.
- **Strict comparison** — Flag any use of `==`, `!=`, `<>` where `===` or `!==` should be used.
- **Curly braces** — All `if`, `else`, `elseif`, `foreach`, `for`, `while`, and `match` bodies must use curly braces, even for single-line bodies.
- **Enum keys** — Enum case names must be TitleCase (e.g., `ActiveSession`, not `active_session`).
- **PHPDoc over inline comments** — Flag inline `//` comments unless the logic is genuinely non-obvious. PHPDoc blocks are preferred. Flag missing `@param`, `@return`, or `@throws` where they aid understanding.
- **Array shapes in PHPDoc** — Flag `array` or `mixed[]` types in PHPDoc that could be expressed as precise array shapes (e.g., `array{id: int, name: string}`).
- **Descriptive names** — Flag abbreviations, single-letter variables (outside of loops), or names that don't convey intent (e.g., `$data`, `$result`, `$temp`).
- **Debug functions** — Flag any use of `dd()`, `dump()`, `ray()`, `var_dump()`, `print_r()`, or `Log::debug()` left in non-test code.

---

#### Category 2 — Security

- **Raw SQL without binding** — Flag `DB::raw()`, `whereRaw()`, `selectRaw()`, `havingRaw()`, `orderByRaw()` that concatenate user-controlled values rather than using bound parameters.
- **Unescaped Blade output** — Flag `{!! $variable !!}` unless the variable is provably safe (e.g., rendered HTML from a trusted internal source). Require explicit justification in a comment.
- **Shell command injection** — Flag `exec()`, `shell_exec()`, `system()`, `passthru()`, `proc_open()`, or `popen()` if any argument is derived from user input or external data.
- **Unsafe deserialization** — Flag `unserialize()` on any value that is not a literal or a value from a fully trusted internal source. Prefer `json_decode()`.
- **Eloquent mass assignment** — Flag Eloquent models that do not define `$fillable` or `$guarded`. Flag `$guarded = []` as high risk.
- **Hardcoded credentials** — Flag hardcoded API keys, passwords, tokens, secrets, or connection strings. These belong in `.env`.
- **Weak hashing for security** — Flag `md5()` or `sha1()` used for passwords, tokens, or any security-sensitive purpose. Use `Hash::make()` or `password_hash()`.
- **File upload validation** — Flag any file upload logic that does not validate MIME type and extension against an allowlist.
- **SSRF** — Flag any HTTP request (`Http::get()`, Guzzle, `file_get_contents()` with a URL) where the URL is user-supplied without allowlist validation.
- **ReDoS** — Flag regular expressions with catastrophic backtracking patterns (nested quantifiers applied to overlapping character classes, e.g., `(a+)+`, `([a-z]*)*`).
- **Unauthenticated routes** — Flag new routes in `routes/web.php` or `routes/api.php` that expose sensitive data or actions without an `auth` middleware or policy check.
- **Authorization gaps** — Flag controller actions that perform writes (create/update/delete) without a corresponding `authorize()` call or `Policy` check.
- **Composer dependency additions** — Flag new entries in `require` or `require-dev` in `composer.json` as requiring explicit human approval.

---

#### Category 3 — Performance

- **N+1 queries** — Flag Eloquent relationships accessed inside loops without eager loading (`with()`/`load()`).
- **Queries inside loops** — Flag any database call (`DB::`, `Model::`, query builder) executed within a `foreach`, `for`, or `while` loop.
- **Collection vs query builder** — Flag cases where a Collection method is used for work the query builder could handle at the database level:
  - `->get()->count()` instead of `->count()`
  - `->get()->first()` instead of `->first()`
  - `->get()->sum('col')` instead of `->sum('col')`
  - `->get()->pluck('col')` instead of `->pluck('col')`
- **Missing database indexes** — Flag columns used in `where()`, `orderBy()`, `join()`, or `groupBy()` clauses in migrations that lack a corresponding `$table->index()` or foreign key index.
- **Unbounded result sets** — Flag `->get()` on queries that could return large or unbounded result sets without `->chunk()` or `->cursor()`.

---

#### Category 4 — Architecture

- **Single Responsibility Principle** — Flag classes that have more than one clear reason to change (e.g., a class that handles both HTTP I/O and business logic, or a service that also formats output).
- **Tight coupling** — Flag direct instantiation of concrete classes (`new ConcreteClass()`) where a contract/interface should be injected instead. Flag hardcoded class references where the container should resolve them.
- **Method length** — Flag any method or function exceeding ~50 lines of executable code. Long methods are a strong signal of SRP violation or missing extraction.
- **Directory structure** — Flag any new files created outside the established structure: `app/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`. New top-level directories require explicit approval.
- **Premature abstractions** — Flag helper classes, traits, or abstract classes that have only one consumer.
- **Fat controllers** — Flag controller methods that contain business logic that belongs in a service, action, or model. Controllers should delegate, not compute.
- **Inertia response shape** — Flag controller methods that return `Inertia::render()` with props that are not typed on the frontend counterpart.

---

#### Category 5 — Testing

- **Coverage for behavior changes** — For every non-trivial logic change in an `app/` file, verify a corresponding test exists in `tests/`. Flag behavior changes with no corresponding Pest test.
- **Deleted tests** — Flag any removed test file or removed test function. Deletion requires explicit justification.
- **`declare(strict_types=1)` in tests** — All test files must have `declare(strict_types=1)`.
- **Placeholder assertions** — Flag `assertTrue(true)`, `expect(true)->toBeTrue()`, or any assertion that always passes regardless of the code under test.
- **Meaningful test names** — Flag `it('works')`, `test('test1')`, or other names that don't describe the behavior being verified.
- **Edge case coverage** — Flag tests that only cover the happy path for logic that has documented error paths, null inputs, empty collections, or boundary values.
- **Raw PHPUnit in Pest files** — Flag `$this->assert*()` style assertions in Pest test files; use Pest's `expect()` API instead.
- **Browser tests** — For Inertia page interactions with complex client-side behavior, flag missing Playwright browser tests (`pestphp/pest-plugin-browser`) where integration testing adds real confidence.

---

#### Category 6 — Laravel Application Conventions

- **Model `$fillable`/`$guarded`** — Every Eloquent model must define `$fillable` or `$guarded`. Flag missing definitions and `$guarded = []`.
- **Route model binding** — Flag controller methods that manually query a model by `id` from the request when route model binding could handle it automatically.
- **Form Request validation** — Flag controller methods that validate directly in the method body using `$request->validate()` for anything beyond trivial 1–2 field validation; prefer dedicated `FormRequest` classes.
- **Policy registration** — Flag model actions (view, create, update, delete) in controllers that do not use a registered `Policy` or `Gate` check.
- **Event/Listener discipline** — Flag direct side-effect logic in controllers (e.g., sending emails, dispatching notifications) that should be delegated to events and listeners or queued jobs.
- **Queue usage** — Flag synchronous execution of slow operations (email, HTTP calls, file processing) inside a request lifecycle that should be queued.
- **Config over hardcoding** — Flag hardcoded environment-specific values (URLs, feature flags, limits) that should be read from `config()` or `.env`.
- **Spatie Permission usage** — Flag `role` or `permission` checks done with raw string comparisons instead of the `spatie/laravel-permission` helpers (`hasRole()`, `can()`, `hasPermissionTo()`).

---

#### Category 7 — Code Smells

- **Dead code** — Flag unreachable statements (code after an unconditional `return`/`throw`), unused private methods, unused class properties, and unused `use` imports.
- **Magic values** — Flag hard-coded numbers or strings used directly in logic without a named constant or config value (e.g., `if ($count > 100)`, `'status' => 'pending'`).
- **Commented-out code** — Flag blocks of code that have been commented out. Remove them; version control preserves history.
- **Boolean trap parameters** — Flag method signatures with multiple boolean parameters. These destroy call-site readability; prefer named arguments or a value object.
- **Nullable type mismatch** — Flag methods declared to return a specific non-nullable type that also return `null` without `?type` in the signature.
- **Unnecessary else after return** — Flag `else` blocks that are only present because the `if` branch always returns. Flatten to early-return style.

---

#### Category 8 — Frontend (TypeScript / React / Inertia)

- **TypeScript `any`** — Flag use of `any` types. Require explicit, precise types or `unknown` with a type guard. This includes implicit `any` from untyped function parameters.
- **Inertia page props typing** — Flag `usePage()` calls that do not provide a typed generic (e.g., `usePage<PageProps>()`). Shared props must be declared in a global `PageProps` interface.
- **Inertia navigation** — Flag `<a href>` tags used for in-app navigation. Use Inertia's `<Link>` component. Flag `window.location` assignments for SPA navigation; use `router.visit()` instead.
- **Inertia form handling** — Flag forms that use `fetch`/`axios` directly for standard CRUD operations where Inertia's `useForm()` or `router.post()` should be used instead.
- **react-hook-form + zod** — Flag forms that validate manually (if/else or inline logic) instead of using `react-hook-form` with a `zod` schema resolver.
- **Component size** — Flag React components exceeding ~150 lines. Large components should be decomposed into smaller, focused components.
- **React hooks rules** — Flag hooks called conditionally or inside loops. Flag `useEffect` with missing or incorrect dependency arrays.
- **Direct DOM manipulation** — Flag `document.getElementById`, `document.querySelector`, or other direct DOM APIs. Use React refs (`useRef`) instead.
- **shadcn/Radix reuse** — Flag custom-built UI primitives (buttons, dialogs, dropdowns, inputs) that duplicate functionality already available in `shadcn/ui`. Prefer reusing existing components from `resources/js/components/ui/`.
- **`console.log` in production code** — Flag any `console.log`, `console.debug`, or `console.warn` left in non-test frontend code.
- **Inline styles** — Flag `style={{ ... }}` usage when equivalent Tailwind utility classes exist. Inline styles should be reserved for truly dynamic values (e.g., computed widths from JS).
- **Missing `key` props** — Flag `.map()` calls that render JSX without a stable, unique `key` prop. Using array index as a key is acceptable only for static, non-reorderable lists.
- **Accessibility basics** — Flag interactive elements (`div`, `span`) used as buttons or links without `role`, `tabIndex`, and keyboard event handlers. Prefer semantic HTML elements.
- **Zod schema location** — Flag inline zod schemas defined inside a component. Schemas should be defined outside the component (module-level or in a dedicated `schemas/` file) to avoid re-creation on every render.

---

### Step 3 — Verify API Correctness with Documentation

For any finding in **Category 4** (architecture) or **Category 6** (application conventions) where you are not certain a pattern is incorrect, verify against the Laravel documentation **before** including it as a finding. Only flag something after confirmation. Do not flag uncertain API usage without checking.

### Step 4 — Compile and Output Findings

1. Group all findings by file path.
2. Within each file, order findings by line number ascending.
3. After all file-level findings, output the **Review Summary** table and verdict.
4. Omit files that have zero findings.
5. If no changed PHP or TypeScript/React files are found, output: `No relevant files changed. Nothing to review.`
