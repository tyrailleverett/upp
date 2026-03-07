I have created the following plan after thorough exploration and analysis of the codebase. Follow the below plan verbatim. Trust the files and references. Do not re-verify what's written in the plan. Explore only when absolutely necessary. First implement all the proposed file changes and then I'll review all the changes together at the end.

---

## Observations

Phases 1–7 established all data models, CRUD operations, public status pages, real-time updates, notifications, webhooks, API, custom domains, and widget. The `Site` model has a `visibility` field (via `SiteVisibility` enum) with `Draft` and `Published` states. The Phase 1 create flow uses a `StoreSiteRequest` and `CreateSiteAction` to create a site with its first components. The Phase 4 public status page controller checks `site.visibility === Published` to render the page. There is currently no guided onboarding — the operator goes directly to a CRUD form.

---

## Approach

This phase creates a multi-step onboarding wizard that runs when an operator first creates a site. The wizard provides a streamlined flow: name the site → add initial components → customize the status page appearance → preview and publish → see the shareable URL. It replaces the default "create site" form for new users creating their first site (returning users creating additional sites still get the standard form if they prefer, but can also use the wizard). The wizard uses a single Inertia page with stepper UI and client-side state management — no intermediate server-side persistence until the user completes or explicitly saves as draft.

---

## - [ ] 1. Onboarding Wizard Controller

**`app/Http/Controllers/Sites/OnboardingWizardController.php`**

- `create(): Response`
  1. Authorize: user must be authenticated
  2. Return `Inertia::render('sites/onboarding/create', ['componentStatuses' => ComponentStatus::cases(), 'siteVisibilities' => SiteVisibility::cases()])`

- `store(StoreOnboardingWizardRequest $request): RedirectResponse`
  1. Call `CreateSiteAction` with site data (name, slug, visibility)
  2. For each component in the request, call `CreateComponentAction` with the new site
  3. If branding data provided (accent_color, meta_title, meta_description), call `UpdateSiteAction`
  4. If the user chose to publish, set visibility to `Published`; otherwise keep as `Draft`
  5. Redirect to the new site's show page flashing the shareable status page URL

---

## - [ ] 2. Form Request

**`app/Http/Requests/Sites/StoreOnboardingWizardRequest.php`**

| Field | Rules |
|---|---|
| `name` | `['required', 'string', 'max:255']` |
| `slug` | `['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('sites', 'slug')]` |
| `visibility` | `['required', Rule::enum(SiteVisibility::class)]` |
| `components` | `['required', 'array', 'min:1', 'max:50']` |
| `components.*.name` | `['required', 'string', 'max:255']` |
| `components.*.description` | `['nullable', 'string', 'max:1000']` |
| `components.*.display_order` | `['required', 'integer', 'min:0']` |
| `accent_color` | `['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/']` |
| `meta_title` | `['nullable', 'string', 'max:255']` |
| `meta_description` | `['nullable', 'string', 'max:500']` |

---

## - [ ] 3. Onboarding Route

Add to `routes/sites.php`:

| Method | URI | Controller | Route Name |
|---|---|---|---|
| GET | `dashboard/sites/onboarding` | `OnboardingWizardController@create` | `sites.onboarding.create` |
| POST | `dashboard/sites/onboarding` | `OnboardingWizardController@store` | `sites.onboarding.store` |

Place BEFORE the `dashboard/sites/{site}` resource routes to avoid `onboarding` being captured as a `{site}` parameter.

---

## - [ ] 4. TypeScript Types

No new model types needed. Add a form type to the onboarding page:

```
OnboardingWizardForm:
  name: string
  slug: string
  visibility: SiteVisibility
  components: { name: string, description: string, display_order: number }[]
  accent_color: string
  meta_title: string
  meta_description: string
```

This type lives in the page component file, not in `models.ts`.

---

## - [ ] 5. Onboarding Wizard Page

**`resources/js/pages/sites/onboarding/create.tsx`**

A multi-step wizard page with client-side state. Uses `useForm` for the final submission.

**Step 1: Name Your Site**
- Inputs: Site name, slug (auto-generated from name with manual override)
- Slug validation: alphanumeric + hyphens, unique check via debounced API call or handle on submit
- Brief explanation of what a site represents

**Step 2: Add Components**
- Dynamic list of components the operator can add/remove/reorder
- Each component: name (required), description (optional)
- Pre-populated with a few suggested defaults (e.g., "Website", "API", "Dashboard") that the user can modify or remove
- Drag-to-reorder or up/down arrow buttons
- Minimum 1 component required

**Step 3: Customize Appearance**
- Accent color picker (hex input + visual preview)
- Meta title (defaults to site name)
- Meta description
- Live preview panel on the right showing a miniature status page with the selected colors and components

**Step 4: Review & Publish**
- Summary of all entered data: site name, slug, component list, branding
- Status page URL preview: `{slug}.statuskit.app`
- Two action buttons:
  - "Save as Draft" — saves with visibility `Draft`, redirects to site dashboard
  - "Publish" — saves with visibility `Published`, redirects to site dashboard with URL flash

**UI Structure**:
- Horizontal stepper at top showing progress (Step 1 of 4, 2 of 4, etc.)
- Back/Next buttons for navigation between steps
- Steps validate before advancing (Step 1 validates name + slug, Step 2 validates at least 1 component)
- Uses client-side form state throughout; only calls `useForm.post()` on final submit

---

## - [ ] 6. Frontend Components

**`resources/js/components/sites/onboarding-stepper.tsx`**

- Props: `{ steps: string[], currentStep: number }`
- Horizontal stepper with numbered circles, labels, and connecting lines
- Active step highlighted, completed steps have checkmark
- Uses Tailwind styling consistent with the app's design system

**`resources/js/components/sites/component-list-builder.tsx`**

- Props: `{ components: ComponentEntry[], onChange: (components: ComponentEntry[]) => void }`
- Renders the add/remove/reorder component list
- Each row has name input, description input, remove button, reorder handle
- "Add Component" button at the bottom
- Pre-populated defaults passed via initial value

**`resources/js/components/sites/status-page-preview.tsx`**

- Props: `{ siteName: string, components: ComponentEntry[], accentColor: string }`
- Renders a scaled-down preview of the public status page
- Shows the overall status banner, component grid with operational status
- Updates in real-time as the user modifies the customization fields

---

## - [ ] 7. Navigation Integration

Update the existing dashboard layout or sites index page to detect if the user has zero sites and redirect them to the onboarding wizard.

**Approach**: In the sites index controller (`SiteController@index`), if the authenticated user has zero sites, redirect to `sites.onboarding.create` instead of rendering the empty sites list. Alternatively, render the index page with an empty state that prominently links to the onboarding wizard.

Recommended: Show the sites index page with an empty state component that links to the onboarding wizard — this is cleaner than a redirect and allows the user to navigate away if they want.

**Empty state for sites index**: When the user has no sites, show a centered illustration/icon, heading "Create your first status page", description text, and a CTA button linking to the onboarding wizard.

Later site creations: Add a "Create Site" button in the sites index header that links to the standard `sites.create` route (the regular form from Phase 1). Optionally, also offer the wizard as an alternative.

---

## - [ ] 8. Tests

### Feature Tests

**`tests/Feature/Sites/OnboardingWizardControllerTest.php`**

- `it displays the onboarding wizard`
- `it creates a site with components through the wizard`
- `it validates required fields`
- `it validates slug uniqueness`
- `it requires at least one component`
- `it saves as draft when draft visibility selected`
- `it publishes when published visibility selected`
- `it applies branding settings`
- `it redirects to the new site after creation`

### Browser Tests

**`tests/Browser/Sites/OnboardingWizardTest.php`**

- `it completes the full onboarding wizard flow`
  - Login → navigate to onboarding → enter site name → see slug auto-generated → next → add components (keep defaults + add one more) → next → set accent color → next → review summary → click "Publish" → redirected to site dashboard → see success flash with URL → visit status page URL → see the public status page with the configured components

- `it saves as draft through the wizard`
  - Login → complete wizard → click "Save as Draft" → site created with Draft visibility

- `it validates step by step`
  - Login → click Next without filling name → see validation error → fill name → next → remove all components → see validation error → add component → next → proceed to review

---

## - [ ] 9. User Journey Flow

```mermaid
flowchart TD
    A[User signs up / logs in] --> B{Has any sites?}
    B -->|No| C[Sites Index — Empty State]
    C --> D[Click "Create your first status page"]
    D --> E[Step 1: Name Your Site]
    E --> F[Step 2: Add Components]
    F --> G[Step 3: Customize Appearance]
    G --> H[Step 4: Review & Publish]
    H -->|Publish| I[Site Created — Published]
    H -->|Save as Draft| J[Site Created — Draft]
    I --> K[Redirect to Site Dashboard]
    J --> K
    K --> L[Flash: Your status page is live at slug.statuskit.app]

    B -->|Yes| M[Sites Index — List of Sites]
    M --> N[Click "Create Site"]
    N --> O[Standard Create Site Form OR Wizard]
```
