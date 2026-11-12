# Aangan — Stage Log

This file tracks progress stage by stage. Update it at the end of every stage.

---

## Stage 1 — Visual & interaction layer ✅ Complete

**Goal:** Build the complete look, layout, navigation, and interactions for all five
screens using clearly labelled demonstration data. No persistence, no real exports,
no payment collection.

### Delivered

**Project setup**
- Laravel 13.42, Livewire 4.4.8, Tailwind CSS 4, Vite 8, PHP 8.3.
- App named **Aangan** (`APP_NAME` in `.env` / `.env.example`).
- Timezone defaults to `Asia/Dhaka` via `APP_TIMEZONE` in `config/app.php`.
- Inter Variable self-hosted from `@fontsource-variable/inter`; Laravel's external
  font plugin removed from Vite so nothing loads from a CDN.

**Design system** (`resources/css/app.css`)
- Palette locked to cream `#ede7c7`, deep red `#8b0000`, burgundy `#5b0202`,
  espresso `#200e01`.
- Reusable surface, card, feature-card, button, field, table, and pill styles.
- Soft shadows, translucent/glass panels, refined 1px borders, rounded corners.
- Custom view-transition CSS for page changes plus a delayed top progress bar.
- Reduced-motion handling for transitions and animated numbers.

**Shell & navigation** (`resources/views/layouts/app.blade.php`)
- Espresso sidebar with brand mark and an animated active indicator.
- Compact mobile header and bottom navigation bar.
- Livewire SPA navigation via `wire:navigate` and `wire:transition.navigate`.
- Five routes only: Dashboard, Buildings, Tenants, Rent, Cashbook.

**Reusable Blade components**
- `icon`, `brand-mark`, `money`, `animated-money`, `page-header`,
  `month-switcher`, `status-pill`, `modal`, `drawer`.

**Screens**
- **Dashboard** — month/year switcher, starting cash/bank, rent collected,
  expenses, closing cash/bank, and the combined closing balance as the primary
  burgundy highlight. Edit-starting-balances modal.
- **Buildings** — minimal cards (name, address, total flats) and Add/Edit forms.
- **Tenants** — name, building, flat, monthly house rent, contact number, search,
  and Add/Edit forms. No extra personal fields.
- **Rent** — month/year selector, building filter, bill table, and a bill drawer
  with House Rent, Electricity, Gas, Car Parking, Service Charge, Optional Other
  Charges, Total, and Cash/Bank payment selection. Electricity is a manually
  entered amount; no meter readings.
- **Cashbook** — Cash / Bank / Combined balance cards, month filter, expense table
  (Expense Date, Expense Description, Paid From, Amount, Actions), Add Expense
  modal, Edit/Delete flows, and PDF/Excel controls.

**Interaction details**
- Single global dialog manager in `resources/js/app.js`: MutationObserver-driven
  scroll lock, focus trap, focus restore, and first-field autofocus.
- Mobile dialogs and the rent drawer behave as bottom sheets.
- Animated sidebar indicator measured from the active item; recalculated on resize
  and after Livewire navigation.
- Animated balances count up on first paint and snap to final values when
  `prefers-reduced-motion` is set.

**Honest Stage 1 boundaries**
- Save, Delete, Collect, receipt download, and PDF/Excel export controls are
  disabled and labelled with the stage that will enable them (`Stage 3`), with short
  explanatory notes.
- All figures come from `app/Support/DemoData.php` and are fictional and internally
  consistent. Default demo month is `2026-11` (November 2026).

### Verification performed

- `php artisan route:list` confirms the five named routes.
- All five routes return HTTP 200 and render their correct screen.
- `npm run build` succeeds and emits self-hosted Inter woff2 assets.
- Headless Edge (CDP) checks at 1440 px and 390 px:
  - exact palette values on body, sidebar, feature card, and primary button;
  - Inter Variable loaded;
  - no horizontal overflow on any screen at either width;
  - sidebar hidden on mobile, bottom navigation visible;
  - no console or runtime errors.
- Interaction checks:
  - sidebar indicator aligns exactly with the active item (top and height match,
    200 ms transition);
  - month switcher moves November 2026 → December 2026;
  - starting-balances modal opens/closes via button, overlay, and Escape, with
    correct focus placement, focus trapping, scroll lock, and focus restore;
  - rent bill drawer lists every charge row, total, and Cash/Bank payment toggle;
  - Cash/Bank selection updates visually;
  - cashbook Add Expense modal focuses the first field and closes cleanly;
  - delete confirmation modal opens and closes;
  - PDF/Excel controls report `disabled`;
  - reduced-motion mode renders final balance values immediately;
  - mobile drawer anchors to the bottom of the viewport.
- Test suite: **12 tests, 107 assertions, all passing**
  (`php artisan test --compact`).
- `vendor/bin/pint --format agent` passes.

### Fixes after first review

- **Unstyled pages from a stale Vite hot file.** A leftover `npm run dev` process
  wrote `public/hot` containing `http://0.0.0.0:5173`. Browsers reject `0.0.0.0`
  (`ERR_ADDRESS_INVALID`), so no CSS or JS loaded and every screen rendered as
  plain text. `vite.config.js` now pins the hot file host to `localhost` via
  `server.hmr.host`, the stale process and hot file were removed, and the app
  serves compiled assets again. README documents the recovery steps.

### Known Stage 1 limitations

- No database models or migrations for buildings, tenants, bills, or expenses.
- Forms accept input but do not persist; Save controls are disabled.
- Month navigation changes the displayed period label and demo dates only; the
  underlying demo amounts stay fixed.
- No authentication or user accounts.
- No PDF/Excel generation and no receipt download.
- `resources/views/welcome.blade.php` is unused (Laravel default, harmless).

---

## Stage 2 — Buildings, Tenants & Rent Collection ✅ Complete

**Goal:** Replace the demonstration layer for Buildings, Tenants, and Rent with real
database records, validation, relationships, monthly billing, and payment collection.
The approved Stage 1 design, palette, layouts, components, and animations are
unchanged. The Dashboard and Cashbook previews were later replaced in Stage 3.

### Database

| Table | Purpose |
| --- | --- |
| `buildings` | Building name and address. |
| `flats` | Unit numbers registered against a building; unique per building. |
| `tenants` | Name, contact number, optional notes. |
| `tenancies` | Links a tenant to a flat with house rent, gas, parking, and service defaults, start/end dates, and an active marker. |
| `rent_bills` | One invoice per tenancy per billing month, with snapshotted charge amounts and `total_due`. |
| `rent_payments` | Cash/Bank payments against a bill, each with its actual payment date. |

Money is stored as `decimal(12,2)` and cast to `decimal:2`. The `tenancies` table has
a unique index on `(flat_id, active_marker)`; ended tenancies store `NULL` in
`active_marker`, so a flat can only have one active tenancy at a time.

### Buildings

- Add and edit buildings with name, address, and a comma-separated list of unit
  numbers.
- Units are created and removed as the list is saved.
- A unit with any tenancy history cannot be removed; the form reports which units
  block the change.
- Cards show total flats and how many are currently occupied.
- No maximum number of buildings or units is hardcoded.

### Tenants

- Add and edit tenants with name, contact number, building, vacant flat, house rent,
  default gas, parking, and service charges, plus optional notes.
- Only vacant flats are offered; the current flat remains selectable while editing.
- Two active tenancies cannot share a flat — enforced by both validation and the
  database unique index.
- Editing an active tenancy updates its charge defaults in place.
- Moving a tenant to a different flat closes the old tenancy and starts a new one so
  historical bills stay attached to the flat they were billed for.
- **End tenancy** records a move-out date, frees the flat, and keeps the tenant,
  tenancy, bills, and payments. Former tenants are labelled in the directory.

### Rent

- Month switcher and building filter.
- Bills are generated idempotently for every tenancy that covers the selected month.
  A unique `(tenancy_id, billing_month)` index prevents duplicate monthly bills.
- House rent, gas, parking, and service charge load from the tenancy defaults;
  electricity and other charges start at zero.
- All six charges remain editable until a payment is recorded. Electricity is
  entered manually each month — no meter readings.
- Bill drawer shows the charge breakdown, live total, Cash/Bank method, payment
  date, amount received, and payment history.
- **Full amount** fills the remaining balance; the method and date are still
  confirmed by pressing **Record payment**.
- Partial payments are supported. Status is derived from the invoice and payments:
  `Unpaid`, `Partially Paid`, or `Paid`.
- A payment recorded in a later month keeps the original `billing_month` and stores
  its actual `paid_on` date.
- Once any payment exists the invoice amounts are locked, preserving historical
  invoice values. Later edits to a tenant's defaults never rewrite existing bills.

### Demonstration data

`PortfolioSeeder` loads the same three buildings, 26 flats, eight tenants, November
2026 bills, and five payments used in Stage 1. Rebuild it with:

```powershell
php artisan migrate:fresh --seed
```

### Verification performed

- Test suite: **28 tests, 175 assertions, all passing**
  (`php artisan test --compact`).
- New `tests/Feature/StageTwoPortfolioTest.php` — 16 tests covering building
  registration and unit syncing, occupied-unit protection, tenant registration,
  duplicate active tenancy prevention, ending a tenancy, rent total calculations,
  idempotent monthly billing, ended-tenancy exclusion, editable bills, full and
  partial payments, later-month payment dates, invoice locking, and invoice
  snapshots.
- `tests/Feature/StageOneScreensTest.php` updated to seed the portfolio and use the
  new model-backed Rent screen.
- Browser flow (headless Edge/CDP): registered a building with units, registered a
  tenant, opened the generated bill, changed electricity, recorded a partial
  payment, and confirmed the updated total, remaining balance, and
  `Partially Paid` status.
- Mobile checks at 390 px: building and tenant modals fit the viewport, footers stay
  visible, bodies scroll without horizontal overflow, the rent drawer opens as a
  bottom sheet, and scroll lock/focus placement still work.
- No console or runtime errors; palette and Inter unchanged.
- `npm run build` succeeds; `vendor/bin/pint app tests database --format agent`
  passes.

### Known Stage 2 limitations

- Dashboard figures, starting balances, and Cashbook balances are still Stage 1
  demonstration data. Collected rent does not yet post to the Cashbook.
- Cashbook expense CRUD, starting-balance persistence, PDF/Excel export, and receipt
  download are not implemented.
- Bills are full-month amounts; mid-month move-ins and move-outs are not prorated.
- A recorded payment cannot be edited or reversed from the UI.
- No authentication or per-user data separation.
- `resources/views/welcome.blade.php` remains unused (Laravel default, harmless).

---

## Stage 3 — Cashbook, Expenses & Monthly Balances ✅ Complete

**Goal:** Replace the remaining Dashboard and Cashbook preview data with live Cash and
Bank balances, persistent monthly starting balances, real expense records, and one
shared monthly calculation. The approved Stage 1 visual identity is unchanged.

### Database

| Table | Purpose |
| --- | --- |
| `monthly_balances` | One row per `YYYY-MM` with starting cash/bank, manual-adjustment flags, and the previous closing balances captured at save time. |
| `expenses` | Expense date, description, amount, and the account paid from (`Cash` or `Bank`). |

Both money columns use `decimal(12,2)`. Expenses are indexed by date for monthly
queries.

### Financial calculation service

`app/Support/Financials.php` is the single source of truth for both screens:

- Rent is recognised in the month of its actual `paid_on` date, not its billing month.
- Expenses are recognised on `expense_date`.
- Cash closing = cash starting + cash rent − cash expenses.
- Bank closing = bank starting + bank rent − bank expenses.
- Combined closing = cash closing + bank closing.
- A month without a saved starting balance rolls forward from the previous month's
  closing balance, calculated recursively and memoized per request.
- Saving a starting balance records the previous closing snapshot and flags a manual
  adjustment when the entered value differs from that closing balance.
- Starting balances and adjustments are never treated as rental income.

### Dashboard

- Live opening and closing Cash and Bank balances, combined closing balance, net
  movement, rent received, and total expenses.
- Rent received uses actual payment dates; billed/outstanding figures use the billing
  month.
- Starting-balances modal now saves values, shows the previous closing balances, and
  warns when a manual difference will be preserved for auditability.
- Month switching recalculates every figure from the selected financial month.

### Cashbook

- Cash, Bank, and Combined total balances at the top with the month/year selector.
- Real expense table in chronological order: Date, Description, Paid From, Amount,
  Actions.
- Add, edit, and delete expenses with validation and delete confirmation.
- Cash expenses reduce Cash; Bank expenses reduce Bank. Editing or deleting an
  expense recalculates the affected month immediately.
- PDF/Excel controls remain disabled; exports are not part of this stage.

### Demonstration data

`PortfolioSeeder` now seeds the November 2026 opening balances and the eight demo
expenses alongside the existing portfolio, bills, and payments. Rebuild with:

```powershell
php artisan migrate:fresh --seed
```

The seeded November 2026 figures remain:

| Item | Amount |
| --- | --- |
| Starting cash / bank | ৳142,000 / ৳586,000 |
| Rent received | ৳202,120 |
| Total expenses | ৳71,790 |
| Closing cash / bank | ৳226,660 / ৳631,670 |
| **Combined closing balance** | **৳858,330** |

### Verification performed

- Test suite: **40 tests, 267 assertions, all passing**
  (`php artisan test --compact`).
- New `tests/Feature/StageThreeFinancialsTest.php` — 12 tests covering manual starting
  balances, previous-closing snapshots, monthly rollover, Cash and Bank rent,
  Cash and Bank expenses, backdated transactions, chronological month filtering,
  expense create/edit/delete, historical adjustments, the Dashboard save/warning flow,
  and the combined closing balance.
- `tests/Feature/StageOneScreensTest.php` updated for the live dashboard/cashbook
  labels and disabled export controls.
- `php artisan migrate:fresh --seed` verified the seeded totals above.
- `npm run build` succeeds; `vendor/bin/pint app tests database --format agent` passes.

### Known Stage 3 limitations

- PDF/Excel export and receipt download are not implemented; their controls stay
  disabled.
- Bills remain full-month amounts; mid-month move-ins and move-outs are not prorated.
- A recorded rent payment cannot be edited or reversed from the UI.
- No authentication or per-user data separation.
- `resources/views/welcome.blade.php` remains unused (Laravel default, harmless).
