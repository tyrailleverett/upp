---
applyTo: "**/*.{ts,tsx}"
---

# TypeScript & React Review (React 19, Inertia v2, Tailwind v4)

## TypeScript Standards
- Use explicit types for function parameters and return values
- Prefer `unknown` over `any` — flag any `any` usage
- Use `const` by default, `let` only when reassignment is needed, never `var`
- Use `for...of` over `.forEach()` and indexed `for` loops
- Use optional chaining (`?.`) and nullish coalescing (`??`)
- Extract magic numbers into named constants

## React Patterns
- Use function components only — no class components
- Hooks must be called at the top level, never conditionally
- All hook dependency arrays must be complete
- Use `key` prop with unique IDs, not array indices
- Never define components inside other components
- Remove `console.log`, `debugger`, and `alert` from production code

## Inertia.js v2
- Pages live in `resources/js/pages/` and use Inertia's `Head` component for meta
- Use `useForm` hook for form handling, not raw fetch/axios
- Use Wayfinder imports from `@/actions/` for route references — no hardcoded URLs
- Deferred props must include a skeleton/loading state

## Accessibility & Security
- Images must have meaningful `alt` text
- Forms must have labels for all inputs
- Use semantic HTML elements (`<button>`, `<nav>`) not divs with roles
- Add `rel="noopener"` on `target="_blank"` links
- Flag `dangerouslySetInnerHTML` usage
- Flag any direct DOM manipulation or `eval()`

## Component Reuse
- Check for existing UI components in `resources/js/components/ui/` before creating new ones
- Follow existing patterns from sibling components
